<?php

namespace App\Enums;

enum StatusTerbit: string
{
    case Draf = 'draf';
    case Terbit = 'terbit';

    public function label(): string
    {
        return match ($this) {
            self::Draf => 'Draf',
            self::Terbit => 'Terbit',
        };
    }
}
