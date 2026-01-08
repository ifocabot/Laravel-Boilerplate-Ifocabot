<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Attendance Automation
|--------------------------------------------------------------------------
*/

// Mark absent employees as ALPHA at end of day
Schedule::command('attendance:mark-alpha')
    ->dailyAt('23:55')
    ->description('Mark absent employees as ALPHA');

// Pre-create attendance rows for tomorrow
Schedule::command('attendance:prepare-rows')
    ->dailyAt('00:10')
    ->description('Pre-create attendance rows for tomorrow');
