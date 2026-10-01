<?php

namespace App\Console\Commands;

use App\Enums\LemburStatus;
use App\Models\Karyawan;
use App\Notifications\LemburMarkedUnpaid;
use App\Services\Shared\WebPushService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MarkUnpaidLembur extends Command
{
    protected $signature = 'lembur:mark-unpaid';

    protected $description = 'Mark approved lembur as unpaid if laporan not submitted before midnight of tgl_lembur';

    public function handle()
    {
        $hariIni = now('Asia/Jakarta')->format('Y-m-d');

        // Lembur yang jadi unpaid (1 kondisi, beda dengan WFH yang butuh laporan + absen pulang):
        // status approved, tgl_lembur sudah lewat, DAN (belum upload laporan ATAU laporan ditolak belum diperbaiki)
        $lemburList = collect();
        try {
            $lemburList = DB::table('lemburs')
                ->where('lemburs.status', LemburStatus::Approved->value)
                ->where('lemburs.tgl_lembur', '<', $hariIni)
                ->where(function ($q) {
                    $q->whereNull('lemburs.laporan_deskripsi')
                        ->orWhere('lemburs.laporan_deskripsi', '')
                        ->orWhere('lemburs.laporan_status', LemburStatus::Rejected->value);
                })
                ->select('lemburs.*')
                ->get();
        } catch (\Throwable $e) {
            Log::error('MarkUnpaidLembur: query lembur gagal: '.$e->getMessage());
            $this->error('Query lembur gagal: '.$e->getMessage());
        }

        if ($lemburList->isEmpty()) {
            $this->info('No lembur records to mark as unpaid.');

            return 0;
        }

        $affected = DB::table('lemburs')
            ->whereIn('id', $lemburList->pluck('id'))
            ->where('status', LemburStatus::Approved->value)
            ->update(['status' => LemburStatus::Unpaid->value]);

        Log::info('MarkUnpaidLembur: '.$affected.' lembur ditandai unpaid', ['ids' => $lemburList->pluck('id')->all()]);
        $this->info("Marked {$affected} lembur records as unpaid.");

        // Kirim notifikasi + web push ke setiap karyawan
        foreach ($lemburList as $lembur) {
            $reason = $this->determineReason($lembur);
            try {
                $karyawan = Karyawan::where('nik', $lembur->nik)->first();

                if ($karyawan) {
                    $karyawan->notify(new LemburMarkedUnpaid($lembur, $reason));
                    app(WebPushService::class)->send(
                        $lembur->nik,
                        'Lembur Unpaid',
                        'Lembur tanggal '.$lembur->tgl_lembur.' ditandai sebagai Unpaid karena '.$reason,
                        '/lembur',
                        'lembur-unpaid-'.$lembur->id
                    );
                }
            } catch (\Throwable $e) {
                Log::error('MarkUnpaidLembur: notifikasi gagal untuk lembur id '.$lembur->id.': '.$e->getMessage());
            }
        }

        $this->info('Notifications sent to '.$lemburList->count().' karyawan.');

        cache()->forget('pending_lembur_count');
        cache()->forget('pending_lembur_admin_count');
        cache()->forget('pending_laporan_lembur_admin_count');

        return 0;
    }

    private function determineReason(object $lembur): string
    {
        if (! empty($lembur->laporan_status) && $lembur->laporan_status === LemburStatus::Rejected->value) {
            return 'laporan ditolak dan belum diperbaiki';
        }

        return 'belum upload laporan';
    }
}
