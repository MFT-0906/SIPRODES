<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Penyimpanan unggahan sederhana: nama berkas acak (hashName), folder per modul, disk `public` untuk gambar.
 * Validasi isi/ukuran dilakukan di FormRequest; layanan ini dilengkapi dan dipusatkan di Fase 8.
 */
class UnggahBerkas
{
    public static function simpanGambar(UploadedFile $berkas, string $folder): string
    {
        return $berkas->store($folder, 'public');
    }

    public static function hapusGambar(?string $path): void
    {
        if (filled($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    public static function urlGambar(?string $path): ?string
    {
        return filled($path) ? Storage::disk('public')->url($path) : null;
    }
}
