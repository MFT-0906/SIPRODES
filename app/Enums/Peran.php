<?php

namespace App\Enums;

enum Peran: string
{
    case SuperAdmin = 'super_admin';
    case Staf = 'staf';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::Staf => 'Staf',
        };
    }
}
