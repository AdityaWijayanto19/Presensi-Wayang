<?php

namespace App\Enums;

enum CutiStatus: string
{
    case Draft = 'draft';
    case PendingAdmin = 'pending_admin';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::PendingAdmin => 'Menunggu Persetujuan',
            self::Approved => 'Disetujui',
            self::Rejected => 'Ditolak',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Draft => 'bg-slate-100 text-slate-600 border border-slate-200',
            self::PendingAdmin => 'bg-amber-100 text-amber-700 border border-amber-200',
            self::Approved => 'bg-emerald-100 text-emerald-700 border border-emerald-200',
            self::Rejected => 'bg-rose-100 text-rose-700 border border-rose-200',
        };
    }
}
