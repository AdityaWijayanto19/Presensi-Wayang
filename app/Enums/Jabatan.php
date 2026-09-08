<?php

namespace App\Enums;

enum Jabatan: string
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
        return [
            self::Intern->value => self::Staff->value,
            self::Staff->value => self::SPV->value,
            self::SPV->value => self::Manager->value,
            self::Manager->value => self::GM->value,
            self::GM->value => self::Direktur->value,
            self::Direktur->value => null,
        ];
    }

    public function atasanJabatan(): ?string
    {
        return self::hierarchy()[$this->value] ?? null;
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
