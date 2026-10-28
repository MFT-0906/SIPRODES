<?php

namespace App\Support;

use Carbon\Carbon;
use Carbon\CarbonInterface;

/**
 * Pemformat tampilan berformat Indonesia (rupiah, persen, tanggal, ukuran berkas).
 * Nilai kosong ditampilkan sebagai tanda hubung agar tidak terlihat seperti angka resmi.
 */
class Format
{
    public static function rupiah(int|float|string|null $nilai): string
    {
        if ($nilai === null || $nilai === '') {
            return 'Rp —';
        }

        return 'Rp '.number_format((float) $nilai, 0, ',', '.');
    }

    public static function persen(int|float|null $nilai, int $desimal = 1): string
    {
        if ($nilai === null) {
            return '—';
        }

        return number_format((float) $nilai, $desimal, ',', '.').'%';
    }

    public static function tanggal(CarbonInterface|string|null $tanggal, bool $panjang = false): string
    {
        if ($tanggal === null || $tanggal === '') {
            return '—';
        }

        return Carbon::parse($tanggal)->locale('id')->translatedFormat($panjang ? 'j F Y' : 'j M Y');
    }

    public static function ukuranBerkas(?int $bytes): string
    {
        if ($bytes === null) {
            return '—';
        }

        if ($bytes >= 1024 * 1024) {
            return number_format($bytes / (1024 * 1024), 1, ',', '.').' MB';
        }

        return number_format(max(1, (int) round($bytes / 1024)), 0, ',', '.').' KB';
    }
}
