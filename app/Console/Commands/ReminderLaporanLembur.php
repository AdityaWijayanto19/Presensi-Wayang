<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Enums\LemburStatus;
use App\Notifications\LemburReminderLaporan;

class ReminderLaporanLembur extends Command
{
    protected $signature = 'lembur:reminder-laporan';
    protected $description = 'Send reminder to employees who haven\'t uploaded laporan for lembur';

    public function handle()
    {
        $hariIni = now('Asia/Jakarta')->format('Y-m-d');

        $lemburList = DB::table('lemburs')
            ->where('status', LemburStatus::Approved->value)
            ->where('tgl_lembur', $hariIni)
            ->where(function ($q) {
                $q->whereNull('laporan_deskripsi')
                  ->orWhere('laporan_deskripsi', '');
            })
            ->get();

        if ($lemburList->isEmpty()) {
            $this->info('No lembur records need reminder. All done!');
            return 0;
        }

        $sentCount = 0;

        foreach ($lemburList as $lembur) {
            $karyawan = \App\Models\Karyawan::where('nik', $lembur->nik)->first();
            if ($karyawan) {
                $karyawan->notify(new LemburReminderLaporan($lembur));

                app(\App\Services\Shared\WebPushService::class)->send(
                    $lembur->nik,
                    'Reminder Upload Laporan Lembur',
                    'Lembur tanggal ' . $lembur->tgl_lembur . ' belum upload laporan! Upload sebelum pukul 00:00.',
                    '/presensi/lembur/' . $lembur->id . '/laporan',
                    'reminder-laporan-lembur-' . $lembur->id
                );

                $sentCount++;
            }
        }

        $this->info("Reminder sent to {$sentCount} karyawan.");

        return 0;
    }
}
