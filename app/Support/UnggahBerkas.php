<?php

namespace App\Support;

use Closure;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    /**
     * PDF disimpan di disk privat (`local`), bukan di web root; hanya bisa diunduh lewat rute yang memeriksa status terbit.
     */
    public static function simpanPdf(UploadedFile $berkas, string $folder): string
    {
        return $berkas->store($folder, 'local');
    }

    public static function hapusPdf(?string $path): void
    {
        if (filled($path)) {
            Storage::disk('local')->delete($path);
        }
    }

    /**
     * Mengirim PDF privat sebagai unduhan (attachment) dengan nama berkas yang aman.
     */
    public static function unduhPdf(string $path, string $judul): StreamedResponse
    {
        return Storage::disk('local')->download(
            $path,
            (Str::slug($judul) ?: 'dokumen').'.pdf',
            ['Content-Type' => 'application/pdf', 'X-Content-Type-Options' => 'nosniff'],
        );
    }

    public static function urlGambar(?string $path): ?string
    {
        return filled($path) ? Storage::disk('public')->url($path) : null;
    }

    /**
     * Aturan validasi PDF: jenis isi, ukuran dari config/desa.php, dan tanda tangan berkas `%PDF-`.
     *
     * @return list<mixed>
     */
    public static function aturanPdf(): array
    {
        return [
            'nullable', 'file', 'mimetypes:application/pdf', 'max:'.config('desa.unggahan.pdf_maks_kb'),
            function (string $atribut, mixed $berkas, Closure $gagal): void {
                if ($berkas instanceof UploadedFile && ! self::tandaTanganPdf($berkas)) {
                    $gagal('Ditolak: hanya PDF yang diizinkan. Pilih berkas lain.');
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function pesanPdf(string $kolom): array
    {
        $mb = round((int) config('desa.unggahan.pdf_maks_kb') / 1024);

        return [
            "{$kolom}.file" => 'Berkas tidak valid.',
            "{$kolom}.mimetypes" => 'Ditolak: hanya PDF yang diizinkan. Pilih berkas lain.',
            "{$kolom}.max" => "Ukuran PDF maksimal {$mb} MB.",
        ];
    }

    public static function tandaTanganPdf(UploadedFile $berkas): bool
    {
        $buka = @fopen($berkas->getRealPath(), 'rb');
        if ($buka === false) {
            return false;
        }
        $awal = fread($buka, 5);
        fclose($buka);

        return $awal === '%PDF-';
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
