<?php

namespace App\Support;

use GdImage;
use RuntimeException;

/**
 * Membuat varian turunan foto hero (public/images/hero-desa.*):
 * - WebP selebar 800/1600/2400 px untuk srcset, agar HP mengunduh foto kecil;
 * - og-desa.jpg 1200×630 untuk pratinjau tautan (WhatsApp, Facebook, dll.);
 * - hero-desa.json, catatan varian beserta ukuran byte foto sumber (dibaca KontenPublik::fotoHero()).
 *
 * Hanya memakai GD bawaan PHP. Jalankan ulang lewat `php artisan desa:foto-hero` setiap kali foto hero diganti.
 */
final class VarianHero
{
    public const LEBAR_WEBP = [800, 1600, 2400];

    /**
     * @param  float  $fokusX  posisi tengah potongan og secara mendatar (0 = kiri, 1 = kanan)
     * @param  float  $fokusY  posisi tengah potongan og secara tegak (0 = atas, 1 = bawah)
     * @param  float  $cakupan  lebar potongan og dibanding lebar foto (0,3–1)
     * @return array{webp: array<int, string>, og: string}
     */
    public static function buat(string $sumber, string $folder, float $fokusX = 0.5, float $fokusY = 0.5, float $cakupan = 1.0): array
    {
        foreach (['imagewebp', 'imagejpeg'] as $fungsi) {
            if (! function_exists($fungsi)) {
                throw new RuntimeException("Fungsi GD {$fungsi}() tidak tersedia di PHP server ini.");
            }
        }

        $info = @getimagesize($sumber);
        $gambar = match ($info[2] ?? null) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($sumber),
            IMAGETYPE_PNG => @imagecreatefrompng($sumber),
            IMAGETYPE_WEBP => @imagecreatefromwebp($sumber),
            default => false,
        };
        if (! $gambar instanceof GdImage) {
            throw new RuntimeException('Foto hero tidak dapat dibaca (harus JPG, PNG, atau WebP).');
        }

        $lebar = imagesx($gambar);
        $tinggi = imagesy($gambar);
        $webp = [];

        foreach (self::LEBAR_WEBP as $w) {
            if ($w > $lebar && $webp !== []) {
                break; // Tidak memperbesar foto; cukup satu varian selebar foto aslinya.
            }
            $target = min($w, $lebar);
            $kecil = $target === $lebar ? $gambar : imagescale($gambar, $target, max(1, (int) round($tinggi * $target / $lebar)));
            $nama = 'hero-desa-'.$w.'.webp';
            imagewebp($kecil, $folder.'/'.$nama, 78);
            $webp[$target] = $nama;
        }

        // Potongan 1200×630 (rasio 1,905:1) di sekitar titik fokus.
        $rasio = 1200 / 630;
        $pw = (int) round($lebar * max(0.3, min(1.0, $cakupan)));
        $ph = (int) round($pw / $rasio);
        if ($ph > $tinggi) {
            $ph = $tinggi;
            $pw = (int) round($ph * $rasio);
        }
        $x = (int) max(0, min($lebar - $pw, round($lebar * $fokusX - $pw / 2)));
        $y = (int) max(0, min($tinggi - $ph, round($tinggi * $fokusY - $ph / 2)));

        $og = imagecreatetruecolor(1200, 630);
        imagecopyresampled($og, $gambar, 0, 0, $x, $y, 1200, 630, $pw, $ph);
        imageinterlace($og, true);
        imagejpeg($og, $folder.'/og-desa.jpg', 84);

        file_put_contents($folder.'/hero-desa.json', json_encode([
            'sumber' => basename($sumber),
            'ukuran_sumber' => filesize($sumber),
            'webp' => $webp,
            'og' => 'og-desa.jpg',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");

        return ['webp' => $webp, 'og' => 'og-desa.jpg'];
    }
}
