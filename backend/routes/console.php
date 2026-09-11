<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Advance the mock delivery simulation every minute: shipments whose transit
// time has elapsed become delivered, completing their sub-orders and orders
// automatically — no customer action required.
Schedule::command('pazarz:simulate-deliveries')->everyMinute();
