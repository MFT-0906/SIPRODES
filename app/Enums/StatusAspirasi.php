<?php

namespace App\Enums;

enum StatusAspirasi: string
{
    case Baru = 'baru';
    case Dibaca = 'dibaca';
    case Selesai = 'selesai';

    public function label(): string
    {
        return match ($this) {
            self::Baru => 'Baru',
            self::Dibaca => 'Sudah Dibaca',
            self::Selesai => 'Selesai',
        };
    }
}
