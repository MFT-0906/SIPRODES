<?php

namespace App\Console\Commands;

use App\Enums\StatusAspirasi;
use App\Models\Aspirasi;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

/**
 * Menganonimkan nama dan NIK aspirasi yang sudah selesai lebih lama dari masa simpan (config/desa.php).
 * Isi pesan dan riwayat penanganan dipertahankan sebagai arsip tanpa identitas pengirim.
 */
#[Signature('aspirasi:anonimkan {--hitung : Hanya menghitung, tanpa mengubah data}')]
#[Description('Anonimkan nama dan NIK aspirasi yang sudah selesai melewati masa simpan')]
class AnonimkanAspirasi extends Command
{
    public function handle(): int
    {
        $bulan = (int) config('desa.aspirasi.masa_simpan_bulan');
        if ($bulan <= 0) {
            $this->warn('Masa simpan tidak diatur (ASPIRASI_MASA_SIMPAN_BULAN); tidak ada yang dianonimkan.');

            return self::SUCCESS;
        }

        $batas = now()->subMonths($bulan);
        $kueri = Aspirasi::query()
            ->where('status', StatusAspirasi::Selesai)
            ->whereNull('dianonimkan_at')
            ->where('selesai_at', '<=', $batas);

        if ($this->option('hitung')) {
            $this->info($kueri->count().' aspirasi memenuhi syarat anonimisasi (selesai sebelum '.$batas->toDateString().').');

            return self::SUCCESS;
        }

        $jumlah = 0;
        $kueri->chunkById(100, function ($kumpulan) use (&$jumlah) {
            foreach ($kumpulan as $aspirasi) {
                $aspirasi->forceFill(['nama' => null, 'nik' => null, 'dianonimkan_at' => now()])->save();
                $jumlah++;
            }
        });

        $this->info("{$jumlah} aspirasi dianonimkan.");

        return self::SUCCESS;
    }
}
