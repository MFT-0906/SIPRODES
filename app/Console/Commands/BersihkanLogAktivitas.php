<?php

namespace App\Console\Commands;

use App\Models\LogAktivitas;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

/**
 * Menghapus entri audit log yang lebih tua dari masa simpan (config/desa.php: log.masa_simpan_bulan).
 */
#[Signature('log:bersihkan {--hitung : Hanya menghitung, tanpa menghapus}')]
#[Description('Hapus entri log aktivitas yang melewati masa simpan')]
class BersihkanLogAktivitas extends Command
{
    public function handle(): int
    {
        $bulan = (int) config('desa.log.masa_simpan_bulan');
        if ($bulan <= 0) {
            $this->warn('Masa simpan log tidak diatur (LOG_AKTIVITAS_MASA_SIMPAN_BULAN); tidak ada yang dihapus.');

            return self::SUCCESS;
        }

        $batas = now()->subMonths($bulan);
        $kueri = LogAktivitas::query()->where('created_at', '<', $batas);

        if ($this->option('hitung')) {
            $this->info($kueri->count().' entri log melewati masa simpan (sebelum '.$batas->toDateString().').');

            return self::SUCCESS;
        }

        $this->info($kueri->delete().' entri log dihapus.');

        return self::SUCCESS;
    }
}
