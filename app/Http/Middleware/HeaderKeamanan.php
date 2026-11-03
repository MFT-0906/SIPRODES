<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

/**
 * Header keamanan dasar untuk semua respons web (NFR-02).
 *
 * CSP: semua aset dilayani dari origin sendiri (font di-host lokal lewat Vite). 'unsafe-eval' diperlukan oleh build
 * standar Alpine.js dan 'unsafe-inline' pada style oleh atribut style (lebar diagram) serta @font-face inline;
 * skrip inline TIDAK diizinkan. Saat server dev Vite aktif (berkas public/hot), origin-nya ikut diizinkan.
 */
class HeaderKeamanan
{
    public function handle(Request $request, Closure $next): Response
    {
        $respons = $next($request);

        $header = [
            'X-Frame-Options' => 'DENY',
            'X-Content-Type-Options' => 'nosniff',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(), payment=(), usb=()',
            'Cross-Origin-Opener-Policy' => 'same-origin',
        ];

        // Saat server dev Vite aktif (berkas public/hot), CSP tidak dikirim: sintaks CSP tidak mendukung alamat IPv6
        // seperti http://[::1]:5173 di banyak peramban, sehingga CSS/JS dev terblokir. CSP penuh berlaku di produksi.
        if ($this->originVite() === null) {
            $header['Content-Security-Policy'] = $this->csp();
        }

        if ($request->isSecure()) {
            $header['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains';
        }

        foreach ($header as $nama => $nilai) {
            if (! $respons->headers->has($nama)) {
                $respons->headers->set($nama, $nilai);
            }
        }

        return $respons;
    }

    private function csp(): string
    {
        $dev = $this->originVite();
        $dev = $dev === null ? '' : ' '.$dev.' '.str_replace(['http://', 'https://'], 'ws://', $dev);

        return implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-eval'".$dev,
            "style-src 'self' 'unsafe-inline'".$dev,
            // Gambar peta (Leaflet): ubin OpenStreetMap dan citra satelit Esri. Tidak ada frame eksternal yang diizinkan.
            "img-src 'self' data: https://tile.openstreetmap.org https://server.arcgisonline.com",
            "font-src 'self' data:".$dev,
            "connect-src 'self'".$dev,
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'none'",
        ]);
    }

    private function originVite(): ?string
    {
        $berkas = Vite::hotFile();
        if (! is_file($berkas)) {
            return null;
        }

        $url = trim((string) file_get_contents($berkas));
        $bagian = parse_url($url);

        return isset($bagian['scheme'], $bagian['host'])
            ? $bagian['scheme'].'://'.$bagian['host'].(isset($bagian['port']) ? ':'.$bagian['port'] : '')
            : null;
    }
}
