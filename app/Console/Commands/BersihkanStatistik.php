<?php

namespace App\Console\Commands;

use App\Models\StatistikKunjungan;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

/**
 * Menghapus agregat statistik kunjungan yang lebih tua dari masa simpan (config/desa.php: statistik.masa_simpan_bulan).
 */
#[Signature('statistik:bersihkan {--hitung : Hanya menghitung, tanpa menghapus}')]
#[Description('Hapus statistik kunjungan yang melewati masa simpan')]
class BersihkanStatistik extends Command
{
    public function handle(): int
    {
        $bulan = (int) config('desa.statistik.masa_simpan_bulan');
        if ($bulan <= 0) {
            $this->warn('Masa simpan statistik tidak diatur (STATISTIK_MASA_SIMPAN_BULAN); tidak ada yang dihapus.');

            return self::SUCCESS;
        }

        $batas = now()->subMonths($bulan)->toDateString();
        $kueri = StatistikKunjungan::query()->where('tanggal', '<', $batas);

        if ($this->option('hitung')) {
            $this->info($kueri->count().' baris statistik melewati masa simpan (sebelum '.$batas.').');

            return self::SUCCESS;
        }

        $this->info($kueri->delete().' baris statistik dihapus.');

        return self::SUCCESS;
    }
}
