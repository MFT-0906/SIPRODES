<?php

namespace App\Console\Commands;

use App\Support\VarianHero;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Throwable;

/**
 * Membuat ulang varian foto hero (WebP kecil untuk HP dan gambar pratinjau tautan 1200×630).
 * Jalankan setiap kali public/images/hero-desa.jpg diganti. Tanpa perintah ini situs tetap jalan,
 * hanya saja memakai foto utama saja dan pratinjau tautan memakai foto utama apa adanya.
 */
#[Signature('desa:foto-hero
    {--fokus-x=0.42 : Titik tengah potongan pratinjau tautan secara mendatar, 0 (kiri) sampai 1 (kanan)}
    {--fokus-y=0.40 : Titik tengah potongan pratinjau tautan secara tegak, 0 (atas) sampai 1 (bawah)}
    {--cakupan=0.75 : Lebar potongan pratinjau dibanding lebar foto, 0.3 sampai 1}')]
#[Description('Buat varian WebP dan gambar pratinjau tautan dari foto hero desa')]
class BuatVarianFotoHero extends Command
{
    public function handle(): int
    {
        $sumber = collect(['jpg', 'jpeg', 'png', 'webp'])
            ->map(fn ($e) => public_path('images/hero-desa.'.$e))
            ->first(fn ($f) => is_file($f));

        if ($sumber === null) {
            $this->error('Foto hero belum ada. Simpan fotonya sebagai public/images/hero-desa.jpg terlebih dahulu.');

            return self::FAILURE;
        }

        try {
            $hasil = VarianHero::buat(
                $sumber,
                public_path('images'),
                (float) $this->option('fokus-x'),
                (float) $this->option('fokus-y'),
                (float) $this->option('cakupan'),
            );
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info('Varian foto hero dibuat: '.implode(', ', [...array_values($hasil['webp']), $hasil['og']]).'.');

        return self::SUCCESS;
    }
}
