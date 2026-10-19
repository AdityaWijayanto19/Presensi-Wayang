<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->hapusDuplikat();

        // Pengaman terakhir: 1 baris presensi per karyawan per tanggal.
        // Menutup race condition "presensi masuk" (beberapa request paralel yang
        // sama-sama tidak melihat baris saat SELECT lalu INSERT bersamaan).
        Schema::table('presensis', function (Blueprint $table) {
            $table->unique(['nik', 'tgl_presensi']);
        });
    }

    public function down(): void
    {
        // Catatan MySQL: index presensis_nik_foreign (lama) otomatis dihapus saat
        // unique (nik, tgl_presensi) dibuat karena dianggap duplikat prefix, sehingga
        // FK nik ikut menumpang di index unique. Kembalikan index nik biasa dulu
        // sebelum unique dibuang, supaya FK tidak kehilangan penyangga.
        if (! Schema::hasIndex('presensis', 'presensis_nik_index')) {
            Schema::table('presensis', function (Blueprint $table) {
                $table->index('nik');
            });
        }

        Schema::table('presensis', function (Blueprint $table) {
            $table->dropUnique(['nik', 'tgl_presensi']);
        });
    }

    /**
     * Bersihkan duplikat (nik, tgl_presensi) yang sudah terlanjur ada.
     *
     * Keeper: baris yang sudah punya data pulang (jam_out terisi) — kalau tidak ada,
     * baris dengan id terkecil (presensi masuk pertama). Data pulang milik duplikat
     * yang belum ada di keeper ikut disalin dulu sebelum barisnya dihapus.
     */
    private function hapusDuplikat(): void
    {
        $duplikat = DB::table('presensis')
            ->select('nik', 'tgl_presensi')
            ->groupBy('nik', 'tgl_presensi')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        $kolomPulang = [
            'jam_out', 'foto_out', 'lokasi_out', 'lokasi_out_akurasi', 'lokasi_out_jarak',
            'gps_fix_out', 'gps_durasi_out_ms', 'ip_out',
        ];

        foreach ($duplikat as $grup) {
            $baris = DB::table('presensis')
                ->where('nik', $grup->nik)
                ->where('tgl_presensi', $grup->tgl_presensi)
                ->orderBy('id')
                ->get();

            $keeperId = ($baris->first(fn ($r) => $r->jam_out !== null) ?? $baris->first())->id;

            foreach ($baris as $barisDuplikat) {
                if ($barisDuplikat->id === $keeperId) {
                    continue;
                }

                // Refetch: baris keeper mungkin baru saja di-update pada iterasi
                // sebelumnya (copy data pulang / gabung flag) supaya tidak tertimpa.
                $keeper = DB::table('presensis')->where('id', $keeperId)->first();

                $update = [];
                foreach ($kolomPulang as $kolom) {
                    if ($keeper->{$kolom} === null && $barisDuplikat->{$kolom} !== null) {
                        $update[$kolom] = $barisDuplikat->{$kolom};
                    }
                }

                $flagBaru = array_diff(
                    array_filter(array_map('trim', explode(',', (string) $barisDuplikat->flag_manipulasi))),
                    array_filter(array_map('trim', explode(',', (string) $keeper->flag_manipulasi))),
                );
                if ($flagBaru !== []) {
                    $gabungan = array_merge(
                        array_filter(array_map('trim', explode(',', (string) $keeper->flag_manipulasi))),
                        $flagBaru,
                    );
                    $update['flag_manipulasi'] = implode(',', array_unique($gabungan));
                }

                if ($update !== []) {
                    DB::table('presensis')->where('id', $keeperId)->update($update);
                }

                DB::table('presensis')->where('id', $barisDuplikat->id)->delete();
            }
        }
    }
};
