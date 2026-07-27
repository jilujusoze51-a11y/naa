<?php

use Illuminate\Support\Facades\Schedule;

/**
 * The auction scheduler.
 *
 * On the server, add ONE cron entry so Laravel can run this:
 *   * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
 *
 * Without that cron entry lots will not close on time.
 */
Schedule::command('auction:close')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();
