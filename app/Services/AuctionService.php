<?php

namespace App\Services;

use App\Models\Vehicle;
use App\Models\Bid;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * AuctionService — the rules of the auction live here, nowhere else.
 *
 * Proxy bidding works like eBay:
 *   You submit a MAXIMUM. The system bids on your behalf in $INCREMENT steps,
 *   only as high as it needs to beat the runner-up — never above your max.
 *
 * CONCURRENCY CONTRACT
 * --------------------
 * Every method that can change a lot's price or status takes a row lock on the
 * vehicle FIRST, then re-reads and re-validates everything from the locked row.
 * Nothing is validated against data read before the lock — that was the source
 * of the price-regression bug.
 */
class AuctionService
{
    public const INCREMENT  = 100;   // minimum raise, in dollars
    public const ANTI_SNIPE = 30;    // if a bid lands within N seconds of close…
    public const EXTEND_BY  = 60;    // …ensure at least N seconds remain

    /**
     * Place a proxy bid.
     * @return array{ok:bool,msg:string,winning?:bool,amount?:float}
     */
    public function placeBid(Vehicle $vehicle, User $user, float $max): array
    {
        // Cheap pre-checks that cannot change under us.
        if (!$user->canBid()) {
            return ['ok' => false, 'msg' => 'Your account must be verified before you can bid.'];
        }
        if ($max <= 0) {
            return ['ok' => false, 'msg' => 'Enter a valid maximum bid.'];
        }

        return DB::transaction(function () use ($vehicle, $user, $max) {

            // FIX: lock first, then read. Everything below uses the locked row.
            $v = Vehicle::whereKey($vehicle->id)->lockForUpdate()->first();

            if (!$v) {
                return ['ok' => false, 'msg' => 'That lot no longer exists.'];
            }

            // FIX: liveness is re-checked inside the lock, not before it.
            if (!$v->isLive()) {
                return ['ok' => false, 'msg' => 'This lot is not live.'];
            }

            // FIX: the minimum is computed from the LOCKED current_bid.
            // Previously this was calculated before the lock, so a bid validated
            // against a stale price could be accepted and then push the price DOWN.
            $minimum = $v->current_bid > 0
                ? (float) $v->current_bid + self::INCREMENT
                : (float) $v->start_bid;

            if ($max < $minimum) {
                return ['ok' => false,
                        'msg' => 'Your maximum must be at least $' . number_format($minimum, 2) . '.'];
            }

            $leader = $this->currentLeader($v);

            // ── nobody has bid yet ────────────────────────────────
            if (!$leader) {
                $settle = $minimum;
                $this->record($v, $user, $settle, $max);
                $this->commit($v, $settle, $user->id);
                $this->antiSnipe($v);   // FIX: a first bid can be a snipe too.

                return ['ok' => true, 'winning' => true, 'amount' => $settle,
                        'msg' => "You're the high bidder at $" . number_format($settle, 2) . '.'];
            }

            // ── you are already the leader: just raise your ceiling ─
            if ((int) $leader->user_id === (int) $user->id) {
                if ($max <= (float) $leader->max_amount) {
                    return ['ok' => false,
                            'msg' => 'You are already the high bidder with a maximum of $'
                                   . number_format($leader->max_amount, 2) . '.'];
                }
                $leader->update(['max_amount' => $max]);

                return ['ok' => true, 'winning' => true, 'amount' => (float) $v->current_bid,
                        'msg' => 'Your maximum was raised to $' . number_format($max, 2) . '.'];
            }

            $rivalMax = (float) $leader->max_amount;

            // ── you outbid them ───────────────────────────────────
            if ($max > $rivalMax) {
                $settle = min($max, $rivalMax + self::INCREMENT);
                $settle = max($settle, $minimum);              // never below the floor
                $this->record($v, $user, $settle, $max);
                $this->commit($v, $settle, $user->id);
                $this->antiSnipe($v);

                return ['ok' => true, 'winning' => true, 'amount' => $settle,
                        'msg' => "You're the high bidder at $" . number_format($settle, 2)
                               . ' — below your $' . number_format($max, 2) . ' maximum.'];
            }

            // ── their proxy beats you (or exactly ties you) ────────
            // FIX: settle can never exceed the rival's ceiling, and can never
            // fall below the price the lot has already reached.
            $settle = min($rivalMax, $max + self::INCREMENT);
            $settle = max($settle, (float) $v->current_bid);

            $this->record($v, $user, $max, $max);                        // your losing bid
            $this->record($v, $leader->user, $settle, $rivalMax);        // their auto-counter
            $this->commit($v, $settle, $leader->user_id);
            $this->antiSnipe($v);

            return ['ok' => true, 'winning' => false, 'amount' => $settle,
                    'msg' => 'Outbid — another bidder\'s maximum is higher. Current bid is $'
                           . number_format($settle, 2) . '.'];
        });
    }

    /** Buy Now — ends the lot instantly. */
    public function buyNow(Vehicle $vehicle, User $user): array
    {
        if (!$user->canBid()) {
            return ['ok' => false, 'msg' => 'Your account must be verified first.'];
        }

        return DB::transaction(function () use ($vehicle, $user) {

            // FIX: this whole method used to run unlocked, so two simultaneous
            // clicks could both pass the "already sold?" check and create two
            // Sale rows for one car.
            $v = Vehicle::whereKey($vehicle->id)->lockForUpdate()->first();

            if (!$v)                        return ['ok' => false, 'msg' => 'That lot no longer exists.'];
            if ((float) $v->buy_now <= 0)   return ['ok' => false, 'msg' => 'This lot has no Buy Now price.'];
            if (in_array($v->status, ['sold', 'unsold'], true)) {
                return ['ok' => false, 'msg' => 'This lot has already closed.'];
            }
            if ((float) $v->current_bid > (float) $v->buy_now) {
                return ['ok' => false, 'msg' => 'Bidding has passed the Buy Now price on this lot.'];
            }

            $price = (float) $v->buy_now;

            $v->update([
                'status'      => 'sold',
                'current_bid' => $price,
                'winner_id'   => $user->id,
                'ends_at'     => now(),
            ]);

            Sale::create([
                'vehicle_id' => $v->id,
                'user_id'    => $user->id,
                'kind'       => 'buynow',
                'amount'     => $price,
                'status'     => 'pending',
            ]);

            return ['ok' => true,
                    'msg' => 'Lot secured at $' . number_format($price, 2)
                           . '. An agent will contact you to finalise payment.'];
        });
    }

    /**
     * Close every live lot whose timer has run out.
     * Safe to call concurrently — each lot is locked and re-checked individually.
     */
    public function closeExpired(): int
    {
        $ids = Vehicle::where('status', 'live')
            ->whereNotNull('ends_at')
            ->where('ends_at', '<=', now())
            ->pluck('id');

        $closed = 0;

        foreach ($ids as $id) {
            $didClose = DB::transaction(function () use ($id) {

                $v = Vehicle::whereKey($id)->lockForUpdate()->first();

                // FIX: re-verify under the lock. A bid landing between the query
                // above and this transaction may have extended the lot (anti-snipe)
                // or another worker may have closed it already.
                if (!$v || $v->status !== 'live' || !$v->ends_at || $v->ends_at->isFuture()) {
                    return false;
                }

                $top = $this->currentLeader($v);

                if ($top && $top->user_id && (float) $v->current_bid >= (float) $v->reserve) {
                    $v->update(['status' => 'sold', 'winner_id' => $top->user_id]);

                    // FIX: firstOrCreate stops a duplicate Sale if this ever
                    // races with buyNow() or a second scheduler run.
                    Sale::firstOrCreate(
                        ['vehicle_id' => $v->id, 'kind' => 'bid'],
                        [
                            'user_id' => $top->user_id,
                            'amount'  => $v->current_bid,
                            'status'  => 'pending',
                        ]
                    );
                } else {
                    $v->update(['status' => 'unsold']);
                }

                return true;
            });

            if ($didClose) {
                $closed++;
            }
        }

        return $closed;
    }

    /** Open a lot for live bidding. */
    public function goLive(Vehicle $v, int $minutes = 10): void
    {
        $minutes = max(1, $minutes);
        $v->update([
            'status'  => 'live',
            'ends_at' => now()->addMinutes($minutes),
        ]);
    }

    // ── internals ────────────────────────────────────────────────

    /**
     * The bid that currently holds the lot.
     *
     * FIX: this used to be $v->bids()->orderByDesc('max_amount')->orderBy('id'),
     * but the bids() relation carries its own orderByDesc('amount'), which is
     * applied FIRST — so the max_amount sort was dead and ties resolved to the
     * wrong bidder. Query the Bid model directly so the ordering is exactly
     * what is written here: highest ceiling wins, earliest submission breaks ties.
     */
    private function currentLeader(Vehicle $v): ?Bid
    {
        return Bid::where('vehicle_id', $v->id)
            ->whereNotNull('user_id')
            ->orderByDesc('max_amount')
            ->orderBy('id')
            ->first();
    }

    private function record(Vehicle $v, ?User $u, float $amount, float $max): void
    {
        Bid::create([
            'vehicle_id'  => $v->id,
            'user_id'     => $u?->id,
            'bidder_name' => $u?->name ?? 'Bidder',
            'amount'      => $amount,
            'max_amount'  => $max,
            'is_bot'      => false,
        ]);
    }

    private function commit(Vehicle $v, float $amount, ?int $winnerId): void
    {
        $v->update(['current_bid' => $amount, 'winner_id' => $winnerId]);
    }

    /**
     * A late bid extends the clock so nobody wins by sniping.
     *
     * FIX: the old test was $v->ends_at->diffInSeconds(now()) <= 30. Under
     * Carbon 3 (shipped with Laravel 11/12) diffInSeconds() returns a SIGNED
     * value, so a lot ending in five minutes evaluated to -300, which is <= 30 —
     * meaning every single bid reset the clock to 60 seconds and silently
     * truncated the auction. isBefore() has no sign ambiguity.
     *
     * The extension also only ever pushes the deadline OUT, never in.
     */
    private function antiSnipe(Vehicle $v): void
    {
        if (!$v->ends_at) {
            return;
        }

        $threshold = now()->addSeconds(self::ANTI_SNIPE);

        if ($v->ends_at->isBefore($threshold)) {
            $extended = now()->addSeconds(self::EXTEND_BY);

            if ($extended->isAfter($v->ends_at)) {
                $v->update(['ends_at' => $extended]);
            }
        }
    }
}
