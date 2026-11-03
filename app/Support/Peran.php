<?php

namespace App\Support;

/**
 * Peran pengguna admin yang sedang masuk, hanya untuk menyesuaikan tampilan.
 * Izin sebenarnya ditegakkan di server lewat Gate `akses-modul` dan middleware `modul:*`.
 */
class Peran
{
    public static function saatIni(): string
    {
        return auth()->user()?->peran->value ?? \App\Enums\Peran::Staf->value;
    }

    public static function superAdmin(): bool
    {
        return auth()->user()?->adalahSuperAdmin() ?? false;
    }
}
