<?php

namespace App\Support;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\URL;

/**
 * Pengaman otomatis lingkungan produksi (NFR-02): tanpa mode debug, HTTPS dipaksa, dan cookie sesi aman.
 * Dipanggil dari AppServiceProvider::boot(); tidak berpengaruh di lingkungan lokal/tes.
 */
class Produksi
{
    public static function terapkan(Application $app): void
    {
        if (! $app->environment('production')) {
            return;
        }

        // Jejak galat dan nilai .env tidak boleh bocor ke pengunjung walau APP_DEBUG salah diisi.
        config(['app.debug' => false]);

        URL::forceScheme('https');

        // Cookie sesi hanya lewat HTTPS, tidak terbaca JavaScript, dan tidak dikirim lintas situs.
        config([
            'session.secure' => true,
            'session.http_only' => true,
            'session.same_site' => 'lax',
        ]);
    }
}
