<?php

namespace App\Console\Commands;

use App\Services\AuctionService;
use Illuminate\Console\Command;

/**
 * FIX: previously nothing closed lots on a schedule. closeExpired() was only
 * invoked opportunistically from six controller actions, which meant a lot with
 * no page traffic stayed "live" past its end time indefinitely, and the winner
 * was not determined until some visitor happened to load a page.
 *
 * Registered in routes/console.php to run every minute.
 */
class CloseExpiredLots extends Command
{
    protected $signature   = 'auction:close';
    protected $description = 'Close any live lot whose timer has expired and queue the sale for approval.';

    public function handle(AuctionService $auction): int
    {
        $closed = $auction->closeExpired();

        if ($closed > 0) {
            $this->info("Closed {$closed} lot(s).");
        }

        return self::SUCCESS;
    }
}
