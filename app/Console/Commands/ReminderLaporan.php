<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Enums\WfhStatus;
use App\Notifications\WfhReminderLaporan;

class ReminderLaporan extends Command
{
    protected $signature = 'wfh:reminder-laporan';
    protected $description = 'Send reminder notification + web push to employees who haven\'t uploaded laporan (H-2 before midnight)';

    public function handle()
    {
        $hariIni = now('Asia/Jakarta')->format('Y-m-d');

        // Query WFH yang approved + tanggal hari ini + (belum upload laporan ATAU laporan ditolak)
        $wfhList = DB::table('wfhs')
            ->where('status', WfhStatus::Approved->value)
            ->where('tgl_wfh', $hariIni)
            ->where(function ($q) {
                $q->whereNull('laporan_deskripsi')
                  ->orWhere('laporan_deskripsi', '')
                  ->orWhere('laporan_status', WfhStatus::Rejected->value);
            })
            ->get();

        if ($wfhList->isEmpty()) {
            $this->info('No WFH records need reminder. All done!');
            return 0;
        }

        $sentCount = 0;

        foreach ($wfhList as $wfh) {
            $karyawan = \App\Models\Karyawan::where('nik', $wfh->nik)->first();
            if ($karyawan) {
                // Kirim notifikasi database
                $karyawan->notify(new WfhReminderLaporan($wfh));

                // Kirim web push
                app(\App\Services\Shared\WebPushService::class)->send(
                    $wfh->nik,
                    '⚠️ Reminder Upload Laporan',
                    'WFH tanggal ' . $wfh->tgl_wfh . ' belum upload laporan! Upload sebelum pukul 00:00.',
                    '/wfh/' . $wfh->id . '/laporan',
                    'reminder-laporan-' . $wfh->id
                );

                $sentCount++;
            }
        }

        $this->info("Reminder sent to {$sentCount} karyawan.");

        return 0;
    }
}
