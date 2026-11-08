<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Warna dan ikon untuk kategori berita. Dipakai bersama oleh sampul berita dan chip filter kategori,
 * sehingga satu kategori selalu tampil dengan warna yang sama di seluruh situs.
 */
final class WarnaKategori
{
    /**
     * @return array{warna: string, lembut: string, ikon: string}
     */
    public static function untuk(?string $kategori): array
    {
        $kunci = Str::lower(trim((string) $kategori));

        [$warna, $lembut, $ikon] = match (true) {
            str_contains($kunci, 'tani') || str_contains($kunci, 'pertanian') => ['#9a6406', '#fff4d6', 'sprout'],
            str_contains($kunci, 'pemerintah') => ['#2d5ba8', '#e6eefb', 'building'],
            str_contains($kunci, 'umum') || str_contains($kunci, 'pengumuman') => ['#b5560f', '#fdeedd', 'message'],
            default => ['#1b5e20', '#e8f1e4', 'users'],
        };

        return ['warna' => $warna, 'lembut' => $lembut, 'ikon' => $ikon];
    }
}
