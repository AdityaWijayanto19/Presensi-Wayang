<?php

namespace App\Enums;

enum IzinStatus: string
{
    case PendingAtasan = 'pending_atasan';
    case PendingAdmin = 'pending_admin';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::PendingAtasan => 'Menunggu Atasan',
            self::PendingAdmin => 'Menunggu HR',
            self::Approved => 'Disetujui',
            self::Rejected => 'Ditolak',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::PendingAtasan => 'bg-amber-50 text-amber-700 border border-amber-200',
            self::PendingAdmin => 'bg-blue-50 text-blue-700 border border-blue-200',
            self::Approved => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
            self::Rejected => 'bg-rose-50 text-rose-700 border border-rose-200',
        };
    }
}
