<?php

namespace App\Enums;

enum ActivityModule: string
{
    case User = 'user';
    case Karyawan = 'karyawan';
    case Unit = 'unit';
    case Presensi = 'presensi';
    case Izin = 'izin';
    case Lembur = 'lembur';
    case Wfh = 'wfh';
    case Cuti = 'cuti';
    case Lainnya = 'lainnya';

    public function label(): string
    {
        return match ($this) {
            self::User => 'Pengguna Admin',
            self::Karyawan => 'Karyawan',
            self::Unit => 'Unit Perusahaan',
            self::Presensi => 'Presensi',
            self::Izin => 'Izin',
            self::Lembur => 'Lembur',
            self::Wfh => 'WFH',
            self::Cuti => 'Cuti',
            self::Lainnya => 'Lainnya',
        };
    }
}
