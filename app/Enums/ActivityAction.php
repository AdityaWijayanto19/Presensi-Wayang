<?php

namespace App\Enums;

enum ActivityAction: string
{
    case Create = 'create';
    case Update = 'update';
    case Delete = 'delete';
    case Approve = 'approve';
    case Reject = 'reject';
    case ResetPassword = 'reset-password';
    case TogglePermission = 'toggle-permission';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Create => 'Tambah',
            self::Update => 'Ubah',
            self::Delete => 'Hapus',
            self::Approve => 'Setujui',
            self::Reject => 'Tolak',
            self::ResetPassword => 'Reset Password',
            self::TogglePermission => 'Ubah Permission',
            self::Other => 'Lainnya',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Create => 'bg-emerald-100 text-emerald-700',
            self::Update => 'bg-blue-100 text-blue-700',
            self::Delete => 'bg-red-100 text-red-700',
            self::Approve => 'bg-teal-100 text-teal-700',
            self::Reject => 'bg-rose-100 text-rose-700',
            self::ResetPassword => 'bg-amber-100 text-amber-700',
            self::TogglePermission => 'bg-purple-100 text-purple-700',
            self::Other => 'bg-slate-100 text-slate-600',
        };
    }
}
