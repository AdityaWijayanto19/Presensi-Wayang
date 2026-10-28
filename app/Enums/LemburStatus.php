<?php

namespace App\Enums;

enum LemburStatus: string
{
    case PendingAtasan = 'pending_atasan';
    case PendingAdmin = 'pending_admin';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Unpaid = 'unpaid';

    /**
     * Status jembatan: super_admin meminta karyawan mengunggah laporan
     * tanpa foto (mulai/selesai). Begitu laporan terkirim, status kembali
     * ke Approved — lihat LemburService::storeLaporanLembur.
     */
    case UploadLaporan = 'upload_laporan';

    public function label(): string
    {
        return match ($this) {
            self::PendingAtasan => 'Menunggu Persetujuan',
            self::PendingAdmin => 'Menunggu Persetujuan HR',
            self::Approved => 'Disetujui',
            self::Rejected => 'Ditolak',
            self::Unpaid => 'Unpaid',
            self::UploadLaporan => 'Upload Laporan',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::PendingAtasan => 'bg-amber-100 text-amber-700 border-amber-200',
            self::PendingAdmin => 'bg-amber-100 text-amber-700 border-amber-200',
            self::Approved => 'bg-emerald-100 text-emerald-700 border-emerald-200',
            self::Rejected => 'bg-rose-100 text-rose-700 border-rose-200',
            self::Unpaid => 'bg-gray-100 text-gray-700 border-gray-200',
            self::UploadLaporan => 'bg-sky-100 text-sky-700 border-sky-200',
        };
    }
}
