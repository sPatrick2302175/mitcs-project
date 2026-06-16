<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;



// Monthly Accrual for VL/SL
Schedule::command('leave:accrue-credits')->monthlyOn(1, '00:00');

// Yearly Reset for SPL/FL (Runs on January 1st at midnight)
Schedule::command('leave:reset-annual')->yearlyOn(1, 1, '00:00');

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
