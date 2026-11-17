<?php

namespace App\Support;

use App\Models\StatistikKunjungan;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * Membaca agregat statistik kunjungan untuk dashboard admin dan widget footer publik.
 *
 * Catatan: "pengunjung" adalah pengunjung unik per hari. Untuk rentang beberapa hari, angka harian dijumlahkan,
 * sehingga orang yang sama yang datang di dua hari berbeda dihitung dua kali.
 */
final class StatistikPengunjung
{
    private const CACHE_PUBLIK = 'statistik.publik';

    private const MENIT_CACHE_PUBLIK = 5;

    /**
     * @return array{pengunjung_hari_ini: int, pengunjung_kemarin: int, pengunjung_7_hari: int, pengunjung_30_hari: int, pengunjung_total: int, tayang_hari_ini: int, tayang_total: int}
     */
    public static function ringkasan(): array
    {
        $hariIni = now()->toDateString();
        $semua = fn () => StatistikKunjungan::query()->where('path', StatistikKunjungan::SEMUA);
        $hariIniBaris = $semua()->where('tanggal', $hariIni)->first();
        $kemarinBaris = $semua()->where('tanggal', now()->subDay()->toDateString())->first();

        return [
            'pengunjung_hari_ini' => (int) ($hariIniBaris?->jumlah_pengunjung ?? 0),
            'pengunjung_kemarin' => (int) ($kemarinBaris?->jumlah_pengunjung ?? 0),
            'pengunjung_7_hari' => (int) $semua()->where('tanggal', '>=', now()->subDays(6)->toDateString())->sum('jumlah_pengunjung'),
            'pengunjung_30_hari' => (int) $semua()->where('tanggal', '>=', now()->subDays(29)->toDateString())->sum('jumlah_pengunjung'),
            'pengunjung_total' => (int) $semua()->sum('jumlah_pengunjung'),
            'tayang_hari_ini' => (int) ($hariIniBaris?->jumlah_tayang ?? 0),
            'tayang_total' => (int) $semua()->sum('jumlah_tayang'),
        ];
    }

    /**
     * Pengunjung dan tayangan 30 hari terakhir (termasuk hari ini), hari tanpa kunjungan bernilai 0.
     *
     * @return list<array{tanggal: string, pengunjung: int, tayang: int}>
     */
    public static function harian30(): array
    {
        $awal = now()->subDays(29)->startOfDay();
        $baris = StatistikKunjungan::query()
            ->where('path', StatistikKunjungan::SEMUA)
            ->where('tanggal', '>=', $awal->toDateString())
            ->get()
            ->keyBy(fn (StatistikKunjungan $b) => $b->tanggal->toDateString());

        $hasil = [];
        for ($i = 0; $i < 30; $i++) {
            $tanggal = $awal->copy()->addDays($i)->toDateString();
            $hasil[] = [
                'tanggal' => $tanggal,
                'pengunjung' => (int) ($baris[$tanggal]->jumlah_pengunjung ?? 0),
                'tayang' => (int) ($baris[$tanggal]->jumlah_tayang ?? 0),
            ];
        }

        return $hasil;
    }

    /**
     * Halaman terpopuler 30 hari terakhir menurut jumlah tayangan.
     *
     * @return list<array{path: string, tayang: int}>
     */
    public static function terpopuler30(int $batas = 10): array
    {
        return StatistikKunjungan::query()
            ->where('path', '!=', StatistikKunjungan::SEMUA)
            ->where('tanggal', '>=', now()->subDays(29)->toDateString())
            ->selectRaw('path, SUM(jumlah_tayang) as tayang')
            ->groupBy('path')
            ->orderByDesc('tayang')
            ->orderBy('path')
            ->limit($batas)
            ->get()
            ->map(fn ($b) => ['path' => (string) $b->path, 'tayang' => (int) $b->tayang])
            ->all();
    }

    /**
     * Angka untuk footer publik, disimpan di cache beberapa menit agar ringan. Null bila dimatikan lewat config
     * atau bila basis data tidak dapat dibaca (footer tetap tampil tanpa angka).
     *
     * @return array{hari_ini: int, kemarin: int, tiga_puluh_hari: int, total: int}|null
     */
    public static function publik(): ?array
    {
        if (! config('desa.statistik.aktif') || ! config('desa.statistik.tampil_publik')) {
            return null;
        }

        try {
            return Cache::remember(self::CACHE_PUBLIK, now()->addMinutes(self::MENIT_CACHE_PUBLIK), function (): array {
                $ringkasan = self::ringkasan();

                return [
                    'hari_ini' => $ringkasan['pengunjung_hari_ini'],
                    'kemarin' => $ringkasan['pengunjung_kemarin'],
                    'tiga_puluh_hari' => $ringkasan['pengunjung_30_hari'],
                    'total' => $ringkasan['pengunjung_total'],
                ];
            });
        } catch (Throwable $e) {
            report($e);

            return null;
        }
    }
}
