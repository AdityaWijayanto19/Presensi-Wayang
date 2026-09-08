<?php

namespace App\Enums;

/**
 * @deprecated Use \App\Enums\Jabatan instead. Kept for backward compatibility.
 */
enum Posisi: string
{
    case Intern = 'Intern';
    case Staff = 'Staff';
    case SPV = 'SPV';
    case Manager = 'Manager';
    case GM = 'GM';
    case Direktur = 'Direktur';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function hierarchy(): array
    {
        return Jabatan::hierarchy();
    }

    public function label(): string
    {
        return match ($this) {
            self::GM => 'General Manager',
            self::SPV => 'Supervisor',
            default => $this->value,
        };
    }
}
