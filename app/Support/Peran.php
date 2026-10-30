<?php

namespace App\Support;

/**
 * Peran pengguna admin yang sedang masuk.
 * Hanya untuk menyesuaikan tampilan; izin sebenarnya WAJIB diperiksa di backend.
 */
class Peran
{
    // TODO(backend): ambil peran dari pengguna yang masuk, bukan dari data contoh atau sesi.

    public static function saatIni(): string
    {
        $peran = DemoContent::admin()['pengguna']['role'];

        // Hanya di lokal: ?peran=super_admin|staf untuk meninjau menu tiap peran (tersimpan di sesi).
        if (app()->environment('local')) {
            $diminta = request()->query('peran');
            if (in_array($diminta, ['super_admin', 'staf'], true)) {
                session(['demo_peran' => $diminta]);
            }
            $peran = session('demo_peran', $peran);
        }

        return $peran;
    }

    public static function superAdmin(): bool
    {
        return self::saatIni() === 'super_admin';
    }
}
