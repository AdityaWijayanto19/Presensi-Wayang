<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('wfh:mark-unpaid')->dailyAt('00:00')->withoutOverlapping();
Schedule::command('wfh:reminder-laporan')->dailyAt('22:00')->withoutOverlapping();
Schedule::command('lembur:reminder-laporan')->dailyAt('22:00')->withoutOverlapping();
