<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduler in-process (tanpa proc_open)
|--------------------------------------------------------------------------
|
| SEMUA event di bawah memakai Schedule::call() + Artisan::call() alih-alih
| Schedule::command(). Schedule::command() menjalankan command sebagai
| SUBPROCESS lewat Symfony Process -> butuh proc_open(), yang DINONAKTIFKAN
| di PHP CLI hosting production (Hostinger shared: disable_functions termasuk
| proc_open). Akibatnya schedule:run "sukses" tapi tiap event gagal diam-diam
| (laravel.log: "The Process class relies on proc_open, which is not
| available") -> lembur/wfh tidak pernah ditandai unpaid, reminder tidak
| pernah terkirim.
|
| Callback Schedule::call() dieksekusi LANGSUNG di dalam proses schedule:run
| (zero dependency proc_open). Command tetap terdaftar & bisa dipanggil
| manual dari CLI untuk backfill/ops. withoutOverlapping() tetap berlaku
| (mutex via cache store database).
|
*/
$scheduleInProcess = function (string $command, string $at) {
    Schedule::call(function () use ($command) {
        Artisan::call($command);
        // Log output ke laravel.log supaya tiap run terdeteksi (observabilitas).
        Log::info('[scheduler] '.$command.' selesai: '.trim(Artisan::output()));
    })->dailyAt($at)->name($command)->withoutOverlapping();
};

$scheduleInProcess('wfh:mark-unpaid', '00:00');
$scheduleInProcess('lembur:mark-unpaid', '00:00');
$scheduleInProcess('wfh:reminder-laporan', '22:00');
$scheduleInProcess('lembur:reminder-laporan', '22:00');
$scheduleInProcess('activitylog:cleanup', '03:15');
