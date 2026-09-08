<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Enums\WfhStatus;
use App\Notifications\WfhMarkedUnpaid;

class MarkUnpaid extends Command
{
    protected $signature = 'wfh:mark-unpaid';
    protected $description = 'Mark approved WFH as unpaid if laporan not submitted or absen pulang missing';

    public function handle()
    {
        $hariIni = date('Y-m-d');

        // Query WFH yang akan jadi unpaid:
        // - status approved
        // - tgl_wfh sudah lewat
        // - laporan kosong ATAU belum absen pulang
        $wfhList = DB::table('wfh')
            ->leftJoin('presensi', function ($join) {
                $join->on('wfh.nik', '=', 'presensi.nik')
                     ->on('wfh.tgl_wfh', '=', 'presensi.tgl_presensi');
            })
            ->where('wfh.status', WfhStatus::Approved->value)
            ->where('wfh.tgl_wfh', '<', $hariIni)
            ->where(function ($q) {
                // Kondisi 1: laporan kosong
                $q->whereNull('wfh.laporan_deskripsi')
                  ->orWhere('wfh.laporan_deskripsi', '');
            })
            ->orWhere(function ($q) use ($hariIni) {
                // Kondisi 2: laporan ada + belum absen pulang
                $q->where('wfh.status', WfhStatus::Approved->value)
                  ->where('wfh.tgl_wfh', '<', $hariIni)
                  ->whereNotNull('wfh.laporan_deskripsi')
                  ->where('wfh.laporan_deskripsi', '!=', '')
                  ->whereNull('presensi.jam_out');
            })
            ->select('wfh.*', 'presensi.jam_out')
            ->get();

        if ($wfhList->isEmpty()) {
            $this->info('No WFH records to mark as unpaid.');
            return 0;
        }

        // Update status ke unpaid
        $affected = DB::table('wfh')
            ->whereIn('id', $wfhList->pluck('id'))
            ->update(['status' => WfhStatus::Unpaid->value]);

        $this->info("Marked {$affected} WFH records as unpaid.");

        // Kirim notifikasi + web push ke setiap karyawan
        foreach ($wfhList as $wfh) {
            $reason = $this->determineReason($wfh);
            $karyawan = \App\Models\Karyawan::where('nik', $wfh->nik)->first();

            if ($karyawan) {
                $karyawan->notify(new WfhMarkedUnpaid($wfh, $reason));
                \App\Services\WfhService::sendWebPush(
                    $wfh->nik,
                    'WFH Unpaid',
                    'WFH tanggal ' . $wfh->tgl_wfh . ' ditandai sebagai Unpaid karena ' . $reason,
                    '/presensi/wfh',
                    'wfh-unpaid-' . $wfh->id
                );
            }
        }

        $this->info('Notifications sent to ' . $wfhList->count() . ' karyawan.');

        cache()->forget('pending_wfh_count');
        cache()->forget('pending_wfh_admin_count');

        return 0;
    }

    private function determineReason(object $wfh): string
    {
        $hasLaporan = !empty($wfh->laporan_deskripsi);

        if (!$hasLaporan) {
            return 'belum upload laporan';
        }

        return 'belum absen pulang';
    }
}
