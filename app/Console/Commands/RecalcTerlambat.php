<?php

namespace App\Console\Commands;

use App\Models\Presensi;
use App\Services\PresensiService;
use Illuminate\Console\Command;

class RecalcTerlambat extends Command
{
    protected $signature = 'presensi:recalc-terlambat {--dry-run : Tampilkan rencana perubahan tanpa mengupdate data}';

    protected $description = 'Hitung ulang kolom terlambat pada seluruh presensi berdasarkan jam masuk unit masing-masing karyawan';

    public function handle(): int
    {
        $presensiService = app(PresensiService::class);
        $dryRun = (bool) $this->option('dry-run');

        $dicek = 0;
        $diubah = 0;
        $dilewati = 0;

        Presensi::with('karyawan')->chunkById(200, function ($rows) use ($presensiService, $dryRun, &$dicek, &$diubah, &$dilewati) {
            foreach ($rows as $presensi) {
                $dicek++;

                $unit = $presensi->karyawan->unit ?? '';
                $jamIn = $presensi->jam_in;

                if ($unit === '' || $jamIn === null || $jamIn === '') {
                    $dilewati++;
                    continue;
                }

                $terlambat = $presensiService->hitungTerlambatPresensi($unit, $jamIn);

                if ($terlambat === (int) $presensi->terlambat) {
                    continue;
                }

                $diubah++;
                if (! $dryRun) {
                    Presensi::whereKey($presensi->id)->update(['terlambat' => $terlambat]);
                }
            }
        });

        $prefix = $dryRun ? '[DRY-RUN] ' : '';
        $this->info(sprintf('%sDicek: %d, Diubah: %d, Dilewati: %d', $prefix, $dicek, $diubah, $dilewati));

        if ($dryRun) {
            $this->comment('Dry-run: tidak ada data yang diupdate. Jalankan ulang tanpa --dry-run untuk menerapkan perubahan.');
        } elseif ($diubah > 0) {
            $this->comment("{$diubah} baris berhasil diperbarui.");
        }

        return self::SUCCESS;
    }
}
