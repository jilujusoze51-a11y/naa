<?php

namespace Tests\Feature;

use App\Models\Bid;
use App\Models\Sale;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\AuctionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression tests for the bugs found in review.
 * Run with:  php artisan test --filter=AuctionTest
 */
class AuctionTest extends TestCase
{
    use RefreshDatabase;

    private function bidder(string $email): User
    {
        return User::create([
            'name' => 'Bidder ' . $email, 'email' => $email,
            'password' => 'password123', 'role' => 'bidder',
            'verified' => true, 'status' => 'active',
        ]);
    }

    private function lot(array $overrides = []): Vehicle
    {
        return Vehicle::create(array_merge([
            'lot' => '01', 'year' => 2020, 'make' => 'BMW', 'model' => 'M4',
            'start_bid' => 10000, 'reserve' => 12000, 'buy_now' => 50000,
            'current_bid' => 0, 'status' => 'live',
            'ends_at' => now()->addMinutes(30),
        ], $overrides));
    }

    /** The headline proxy rule: you win at one increment over the runner-up. */
    public function test_proxy_settles_just_above_the_rival_not_at_your_max(): void
    {
        $svc = app(AuctionService::class);
        $v   = $this->lot();

        $svc->placeBid($v, $this->bidder('a@t.test'), 18000);
        $r = $svc->placeBid($v->fresh(), $this->bidder('b@t.test'), 20000);

        $this->assertTrue($r['ok']);
        $this->assertSame(18100.0, (float) $r['amount'], 'Should settle at rival max + increment');
        $this->assertSame(18100.0, (float) $v->fresh()->current_bid);
    }

    /** BUG: anti-snipe fired on every bid and truncated the lot to 60 seconds. */
    public function test_a_bid_far_from_close_does_not_shorten_the_lot(): void
    {
        $svc = app(AuctionService::class);
        $v   = $this->lot(['ends_at' => now()->addMinutes(30)]);
        $was = $v->ends_at->copy();

        $svc->placeBid($v, $this->bidder('a@t.test'), 15000);

        $this->assertTrue(
            $v->fresh()->ends_at->greaterThanOrEqualTo($was),
            'Anti-snipe must never pull the deadline in'
        );
        $this->assertGreaterThan(600, $v->fresh()->secondsLeft(), 'Lot was truncated');
    }

    /** Anti-snipe must still work when it is genuinely needed. */
    public function test_a_late_bid_extends_the_lot(): void
    {
        $svc = app(AuctionService::class);
        $v   = $this->lot(['ends_at' => now()->addSeconds(10)]);

        $svc->placeBid($v, $this->bidder('a@t.test'), 15000);

        $this->assertGreaterThan(30, $v->fresh()->secondsLeft(), 'Late bid should extend');
    }

    /** BUG: a stale minimum let a low bid drag the price back down. */
    public function test_price_never_moves_backwards(): void
    {
        $svc = app(AuctionService::class);
        $v   = $this->lot();

        $svc->placeBid($v, $this->bidder('a@t.test'), 40000);
        $high = (float) $v->fresh()->current_bid;

        // A stale handle still holding the old price, as a slow request would.
        $stale = $v->fresh();
        $stale->current_bid = 0;
        $svc->placeBid($stale, $this->bidder('b@t.test'), 11000);

        $this->assertGreaterThanOrEqual($high, (float) $v->fresh()->current_bid);
    }

    /** BUG: leader was resolved by amount, so exact ties handed over the lot. */
    public function test_an_exact_tie_leaves_the_original_leader_in_front(): void
    {
        $svc = app(AuctionService::class);
        $v   = $this->lot();
        $a   = $this->bidder('a@t.test');

        $svc->placeBid($v, $a, 20000);
        $svc->placeBid($v->fresh(), $this->bidder('b@t.test'), 20000);

        $this->assertSame($a->id, (int) $v->fresh()->winner_id, 'First to that max wins the tie');
    }

    /** BUG: buyNow ran unlocked and could sell one car twice. */
    public function test_buy_now_cannot_sell_the_same_lot_twice(): void
    {
        $svc = app(AuctionService::class);
        $v   = $this->lot();

        $first  = $svc->buyNow($v, $this->bidder('a@t.test'));
        $second = $svc->buyNow($v->fresh(), $this->bidder('b@t.test'));

        $this->assertTrue($first['ok']);
        $this->assertFalse($second['ok'], 'Second Buy Now must be rejected');
        $this->assertSame(1, Sale::where('vehicle_id', $v->id)->count());
    }

    public function test_unverified_users_cannot_bid(): void
    {
        $u = $this->bidder('u@t.test');
        $u->update(['verified' => false]);

        $r = app(AuctionService::class)->placeBid($this->lot(), $u, 20000);
        $this->assertFalse($r['ok']);
    }

    public function test_expired_lot_below_reserve_goes_unsold(): void
    {
        $svc = app(AuctionService::class);
        $v   = $this->lot(['reserve' => 30000]);

        $svc->placeBid($v, $this->bidder('a@t.test'), 12000);
        $v->update(['ends_at' => now()->subMinute()]);

        $svc->closeExpired();

        $this->assertSame('unsold', $v->fresh()->status);
        $this->assertSame(0, Sale::where('vehicle_id', $v->id)->count());
    }

    public function test_expired_lot_above_reserve_creates_one_pending_sale(): void
    {
        $svc = app(AuctionService::class);
        $v   = $this->lot(['reserve' => 11000]);

        $svc->placeBid($v, $this->bidder('a@t.test'), 25000);
        $v->update(['ends_at' => now()->subMinute()]);

        $svc->closeExpired();
        $svc->closeExpired();   // second run must not duplicate

        $this->assertSame('sold', $v->fresh()->status);
        $this->assertSame(1, Sale::where('vehicle_id', $v->id)->count());
    }
}
