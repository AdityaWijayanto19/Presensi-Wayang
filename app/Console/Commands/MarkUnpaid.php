<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        // 1. Status approved, tgl_wfh sudah lewat, BELUM upload laporan ATAU laporan ditolak
        // 2. Status approved, tgl_wfh sudah lewat, SUDAH upload laporan tapi BELUM absen pulang
        $wfhBelumLaporan = collect();
        try {
            $wfhBelumLaporan = DB::table('wfhs')
                ->where('wfhs.status', WfhStatus::Approved->value)
                ->where('wfhs.tgl_wfh', '<', $hariIni)
                ->where(function ($q) {
                    $q->whereNull('wfhs.laporan_deskripsi')
                      ->orWhere('wfhs.laporan_deskripsi', '')
                      ->orWhere('wfhs.laporan_status', WfhStatus::Rejected->value);
                })
                ->select('wfhs.*')
                ->get();
        } catch (\Throwable $e) {
            Log::error('MarkUnpaid: query wfhBelumLaporan gagal: ' . $e->getMessage());
            $this->error('Query wfhBelumLaporan gagal: ' . $e->getMessage());
        }

        $wfhBelumPulang = collect();
        try {
            $wfhBelumPulang = DB::table('wfhs')
                ->leftJoin('presensis', function ($join) {
                    $join->on('wfhs.nik', '=', 'presensis.nik')
                         ->on('wfhs.tgl_wfh', '=', 'presensis.tgl_presensi');
                })
                ->where('wfhs.status', WfhStatus::Approved->value)
                ->where('wfhs.tgl_wfh', '<', $hariIni)
                ->whereNotNull('wfhs.laporan_deskripsi')
                ->where('wfhs.laporan_deskripsi', '!=', '')
                ->where('wfhs.laporan_status', '!=', WfhStatus::Rejected->value)
                ->whereNull('presensis.jam_out')
                ->select('wfhs.*')
                ->distinct()
                ->get();
        } catch (\Throwable $e) {
            Log::error('MarkUnpaid: query wfhBelumPulang gagal: ' . $e->getMessage());
            $this->error('Query wfhBelumPulang gagal: ' . $e->getMessage());
        }

        $wfhList = $wfhBelumLaporan->concat($wfhBelumPulang)->unique('id');

        if ($wfhList->isEmpty()) {
            $this->info('No WFH records to mark as unpaid.');
            return 0;
        }

        $affected = DB::table('wfhs')
            ->whereIn('id', $wfhList->pluck('id'))
            ->update(['status' => WfhStatus::Unpaid->value]);

        Log::info('MarkUnpaid: ' . $affected . ' WFH ditandai unpaid', ['ids' => $wfhList->pluck('id')->all()]);
        $this->info("Marked {$affected} WFH records as unpaid.");

        // Kirim notifikasi + web push ke setiap karyawan
        foreach ($wfhList as $wfh) {
            $reason = $this->determineReason($wfh);
            try {
                $karyawan = \App\Models\Karyawan::where('nik', $wfh->nik)->first();

                if ($karyawan) {
                    $karyawan->notify(new WfhMarkedUnpaid($wfh, $reason));
                    app(\App\Services\Shared\WebPushService::class)->send(
                        $wfh->nik,
                        'WFH Unpaid',
                        'WFH tanggal ' . $wfh->tgl_wfh . ' ditandai sebagai Unpaid karena ' . $reason,
                        '/wfh',
                        'wfh-unpaid-' . $wfh->id
                    );
                }
            } catch (\Throwable $e) {
                Log::error('MarkUnpaid: notifikasi gagal untuk WFH id ' . $wfh->id . ': ' . $e->getMessage());
            }
        }

        $this->info('Notifications sent to ' . $wfhList->count() . ' karyawan.');

        cache()->forget('pending_wfh_count');
        cache()->forget('pending_wfh_admin_count');

        return 0;
    }

    private function determineReason(object $wfh): string
    {
        if (!empty($wfh->laporan_status) && $wfh->laporan_status === WfhStatus::Rejected->value) {
            return 'laporan ditolak dan belum diperbaiki';
        }

        $hasLaporan = !empty($wfh->laporan_deskripsi);

        if (!$hasLaporan) {
            return 'belum upload laporan';
        }

        return 'belum absen pulang';
    }
}
