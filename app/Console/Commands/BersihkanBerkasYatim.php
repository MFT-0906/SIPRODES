<?php

namespace App\Console\Commands;

use App\Models\ApbdesDokumen;
use App\Models\Berita;
use App\Models\Komoditas;
use App\Models\PerangkatDesa;
use App\Models\Umkm;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Mencari (dan opsional menghapus) berkas unggahan yang tidak dirujuk database, mis. sisa proses yang gagal di tengah.
 * Berkas yang lebih muda dari 24 jam tidak disentuh agar unggahan yang sedang berjalan aman.
 */
#[Signature('unggahan:bersihkan {--hapus : Hapus berkas yatim (tanpa opsi ini hanya melaporkan)}')]
#[Description('Laporkan atau hapus berkas unggahan yang tidak dipakai database')]
class BersihkanBerkasYatim extends Command
{
    private const UMUR_MINIMUM_DETIK = 86400;

    public function handle(): int
    {
        $sasaran = [
            ['public', 'berita', Berita::query()->whereNotNull('gambar_path')->pluck('gambar_path')],
            ['public', 'perangkat', PerangkatDesa::query()->whereNotNull('foto_path')->pluck('foto_path')],
            ['public', 'komoditas', Komoditas::query()->whereNotNull('foto_path')->pluck('foto_path')],
            ['public', 'umkm', Umkm::query()->whereNotNull('foto_path')->pluck('foto_path')],
            ['local', 'apbdes', ApbdesDokumen::query()->whereNotNull('path')->pluck('path')],
        ];

        $yatim = 0;
        $dihapus = 0;

        foreach ($sasaran as [$disk, $folder, $dipakai]) {
            $dipakai = $dipakai->flip();
            $penyimpanan = Storage::disk($disk);

            foreach ($penyimpanan->allFiles($folder) as $berkas) {
                if ($dipakai->has($berkas) || now()->timestamp - $penyimpanan->lastModified($berkas) < self::UMUR_MINIMUM_DETIK) {
                    continue;
                }

                $yatim++;
                $this->line("Yatim: [{$disk}] {$berkas}");

                if ($this->option('hapus') && $penyimpanan->delete($berkas)) {
                    $dihapus++;
                }
            }
        }

        $this->info($this->option('hapus') ? "{$dihapus} dari {$yatim} berkas yatim dihapus." : "{$yatim} berkas yatim ditemukan (jalankan dengan --hapus untuk menghapus).");

        return self::SUCCESS;
    }
}
