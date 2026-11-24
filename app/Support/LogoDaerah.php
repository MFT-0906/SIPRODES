<?php

namespace App\Support;

/**
 * Logo resmi pemerintah daerah (config desa.logo) untuk komponen identitas situs.
 *
 * Berkas logo tidak disertakan di repo: pengelola situs mengunggah berkas resmi ke folder public.
 * Bila berkas tidak ada atau nama berkasnya tidak aman, data() mengembalikan null sehingga
 * identitas tampil sebagai tulisan saja.
 */
class LogoDaerah
{
    private const POLA_BERKAS = '#^[a-z0-9_-]+(/[a-z0-9_.-]+)*\.(png|webp|jpg|jpeg|svg)$#i';

    private const UKURAN_BAWAAN = 44;

    /**
     * @return array{url: string, alt: string, lebar: int, tinggi: int}|null
     */
    public static function data(): ?array
    {
        $berkas = ltrim((string) config('desa.logo.berkas'), '/');
        $jalur = public_path($berkas);

        if ($berkas === '' || str_contains($berkas, '..') || ! preg_match(self::POLA_BERKAS, $berkas) || ! is_file($jalur)) {
            return null;
        }

        [$lebar, $tinggi] = self::ukuran($jalur);

        return [
            'url' => asset($berkas).'?v='.filemtime($jalur),
            'alt' => (string) config('desa.logo.alt', 'Logo pemerintah daerah'),
            'lebar' => $lebar,
            'tinggi' => $tinggi,
        ];
    }

    /**
     * Ukuran asli dipakai untuk atribut width/height agar tata letak tidak bergeser saat gambar dimuat.
     *
     * @return array{0: int, 1: int}
     */
    private static function ukuran(string $jalur): array
    {
        $info = @getimagesize($jalur);
        if (is_array($info) && $info[0] > 0 && $info[1] > 0) {
            return [(int) $info[0], (int) $info[1]];
        }

        // SVG: baca viewBox bila ada.
        $isi = @file_get_contents($jalur, false, null, 0, 4096) ?: '';
        if (preg_match('/viewBox\s*=\s*"[\d.\-]+[\s,]+[\d.\-]+[\s,]+([\d.]+)[\s,]+([\d.]+)"/i', $isi, $m) && (float) $m[1] > 0 && (float) $m[2] > 0) {
            return [(int) round((float) $m[1]), (int) round((float) $m[2])];
        }

        return [self::UKURAN_BAWAAN, self::UKURAN_BAWAAN];
    }
}
