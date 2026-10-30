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

    public static function tanggalLengkap(CarbonInterface|string|null $tanggal): string
    {
        if ($tanggal === null || $tanggal === '') {
            return '—';
        }

        return Carbon::parse($tanggal)->locale('id')->translatedFormat('l, j F Y');
    }

    /**
     * Tanggal agenda untuk lencana kartu, mis. ['05', 'NOV'].
     *
     * @return array{0: string, 1: string}
     */
    public static function hariBulan(CarbonInterface|string $tanggal): array
    {
        $tanggal = Carbon::parse($tanggal)->locale('id');

        return [$tanggal->format('d'), mb_strtoupper($tanggal->translatedFormat('M'))];
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
    /**
     * NIK disamarkan: hanya 4 digit terakhir yang terlihat. Aspirasi tidak pernah menampilkan NIK utuh.
     */
    public static function nikSamar(?string $nik): ?string
    {
        $digit = preg_replace('/\D/', '', (string) $nik);

        if ($digit === '') {
            return null;
        }

        return implode(' ', mb_str_split(str_repeat('•', max(0, strlen($digit) - 4)).substr($digit, -4), 4));
    }
}
