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
        $hariIni = now('Asia/Jakarta')->format('Y-m-d');

        // Query WFH yang akan jadi unpaid:
        // 1. Status approved, tgl_wfh sudah lewat, BELUM upload laporan
        // 2. Status approved, tgl_wfh sudah lewat, SUDAH upload laporan tapi BELUM absen pulang
        $wfhBelumLaporan = DB::table('wfhs')
            ->where('wfhs.status', WfhStatus::Approved->value)
            ->where('wfhs.tgl_wfh', '<', $hariIni)
            ->where(function ($q) {
                $q->whereNull('wfhs.laporan_deskripsi')
                  ->orWhere('wfhs.laporan_deskripsi', '');
            })
            ->select('wfhs.*')
            ->get();

        $wfhBelumPulang = DB::table('wfhs')
            ->leftJoin('presensis', function ($join) {
                $join->on('wfhs.nik', '=', 'presensis.nik')
                     ->on('wfhs.tgl_wfh', '=', 'presensis.tgl_presensi');
            })
            ->where('wfhs.status', WfhStatus::Approved->value)
            ->where('wfhs.tgl_wfh', '<', $hariIni)
            ->whereNotNull('wfhs.laporan_deskripsi')
            ->where('wfhs.laporan_deskripsi', '!=', '')
            ->whereNull('presensis.jam_out')
            ->select('wfhs.*', 'presensis.jam_out')
            ->groupBy('wfhs.id')
            ->get();

        $wfhList = $wfhBelumLaporan->concat($wfhBelumPulang)->unique('id');

        if ($wfhList->isEmpty()) {
            $this->info('No WFH records to mark as unpaid.');
            return 0;
        }

        $affected = DB::table('wfhs')
            ->whereIn('id', $wfhList->pluck('id'))
            ->update(['status' => WfhStatus::Unpaid->value]);

        $this->info("Marked {$affected} WFH records as unpaid.");

        // Kirim notifikasi + web push ke setiap karyawan
        foreach ($wfhList as $wfh) {
            $reason = $this->determineReason($wfh);
            $karyawan = \App\Models\Karyawan::where('nik', $wfh->nik)->first();

            if ($karyawan) {
                $karyawan->notify(new WfhMarkedUnpaid($wfh, $reason));
                app(\App\Services\Shared\WebPushService::class)->send(
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
