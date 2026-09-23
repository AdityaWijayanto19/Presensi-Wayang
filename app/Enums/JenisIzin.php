<?php

namespace App\Enums;

enum JenisIzin: string
{
    case TidakMasuk = 'tidak_masuk';
    case Terlambat = 'terlambat';
    case SetengahHari = 'setengah_hari';
    case PulangCepat = 'pulang_cepat';
    case Sakit = 'sakit';

    public function label(): string
    {
        return match ($this) {
            self::TidakMasuk => 'Izin Tidak Masuk',
            self::Terlambat => 'Izin Terlambat',
            self::SetengahHari => 'Izin Setengah Hari',
            self::PulangCepat => 'Izin Pulang Cepat',
            self::Sakit => 'Sakit',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::TidakMasuk => 'bg-amber-50 text-amber-700 border border-amber-200',
            self::Terlambat => 'bg-orange-50 text-orange-700 border border-orange-200',
            self::SetengahHari => 'bg-indigo-50 text-indigo-700 border border-indigo-200',
            self::PulangCepat => 'bg-cyan-50 text-cyan-700 border border-cyan-200',
            self::Sakit => 'bg-rose-50 text-rose-700 border border-rose-200',
        };
    }
}
