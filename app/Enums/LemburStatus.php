<?php

namespace App\Enums;

enum LemburStatus: string
{
    case PendingAtasan = 'pending_atasan';
    case PendingAdmin = 'pending_admin';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::PendingAtasan => 'Menunggu Persetujuan',
            self::PendingAdmin => 'Menunggu Persetujuan HR',
            self::Approved => 'Disetujui',
            self::Rejected => 'Ditolak',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::PendingAtasan => 'bg-amber-100 text-amber-700 border-amber-200',
            self::PendingAdmin => 'bg-amber-100 text-amber-700 border-amber-200',
            self::Approved => 'bg-emerald-100 text-emerald-700 border-emerald-200',
            self::Rejected => 'bg-rose-100 text-rose-700 border-rose-200',
        };
    }
}
