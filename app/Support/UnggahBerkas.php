<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Penyimpanan unggahan sederhana: nama berkas acak (hashName), folder per modul, disk `public` untuk gambar.
 * Aturan validasi gambar dipusatkan di sini; layanan ini dilengkapi untuk semua jenis berkas di Fase 8.
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

    /**
     * Aturan validasi gambar dari config/desa.php (jenis isi berkas, ukuran, dimensi).
     *
     * @return list<string>
     */
    public static function aturanGambar(bool $persegi = false): array
    {
        $unggah = config('desa.unggahan');
        $dimensi = 'max_width='.$unggah['gambar_maks_piksel'].',max_height='.$unggah['gambar_maks_piksel'];

        return [
            'nullable', 'file',
            'mimetypes:'.implode(',', $unggah['gambar_mimetypes']),
            'max:'.$unggah['gambar_maks_kb'],
            'dimensions:'.$dimensi.($persegi ? ',ratio=1/1' : ''),
        ];
    }

    /**
     * Pesan galat untuk aturan di atas, dengan kunci berawalan nama kolom.
     *
     * @return array<string, string>
     */
    public static function pesanGambar(string $kolom, bool $persegi = false): array
    {
        $kb = (int) config('desa.unggahan.gambar_maks_kb');
        $px = (int) config('desa.unggahan.gambar_maks_piksel');

        return [
            "{$kolom}.file" => 'Berkas gambar tidak valid.',
            "{$kolom}.mimetypes" => 'Gambar harus berformat JPG, PNG, atau WebP.',
            "{$kolom}.max" => 'Ukuran gambar maksimal '.round($kb / 1024, 1).' MB.',
            "{$kolom}.dimensions" => $persegi
                ? "Gambar harus persegi (rasio 1:1) dan maksimal {$px} × {$px} piksel."
                : "Dimensi gambar maksimal {$px} × {$px} piksel.",
        ];
    }
}
