<?php

namespace App\Support;

use Closure;
use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Layanan unggahan terpusat (TEC-06) untuk semua modul.
 *
 * - Nama berkas selalu acak dan ekstensi ditentukan dari isi berkas, bukan dari nama kiriman pengguna.
 * - Satu folder per modul. Gambar ke disk `public` (dapat diakses web); PDF ke disk `local` (privat).
 * - Gambar dibaca ulang dan ditulis ulang (re-encode) sehingga hanya piksel yang tersimpan: metadata (EXIF/GPS),
 *   komentar, dan muatan tersembunyi (mis. kode PHP yang ditempel di akhir berkas) tidak ikut tersimpan.
 * - Validasi isi berkas dilakukan lewat aturan di bawah (aturanGambar/aturanPdf); pesan galat sama dengan uploader FE.
 */
class UnggahBerkas
{
    // ---- Gambar (disk public) ----------------------------------------------------------------------

    /**
     * Menyimpan gambar yang sudah lolos validasi: dibaca, diputar sesuai EXIF, dikecilkan bila lebih panjang dari
     * batas sisi panjang, lalu ditulis ulang sebagai JPG/PNG/WebP dengan nama acak.
     *
     * @throws ValidationException bila isi berkas bukan gambar yang dapat diproses
     */
    public static function simpanGambar(UploadedFile $berkas, string $folder, string $kolom = 'gambar'): string
    {
        $info = @getimagesize($berkas->getRealPath());
        $jenis = $info[2] ?? null;
        $ekstensi = match ($jenis) {
            IMAGETYPE_JPEG => 'jpg',
            IMAGETYPE_PNG => 'png',
            IMAGETYPE_WEBP => 'webp',
            default => null,
        };

        if ($info === false || $ekstensi === null) {
            throw self::galat($kolom, 'Berkas gambar tidak valid atau rusak.');
        }

        self::pastikanMemoriCukup($info[0], $info[1], $kolom);

        $gambar = match ($jenis) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($berkas->getRealPath()),
            IMAGETYPE_PNG => @imagecreatefrompng($berkas->getRealPath()),
            default => @imagecreatefromwebp($berkas->getRealPath()),
        };

        if (! $gambar instanceof GdImage) {
            throw self::galat($kolom, 'Berkas gambar tidak valid atau rusak.');
        }

        if ($jenis === IMAGETYPE_JPEG) {
            $gambar = self::putarSesuaiExif($gambar, $berkas->getRealPath());
        }
        $gambar = self::kecilkan($gambar);

        imagealphablending($gambar, false);
        imagesavealpha($gambar, true);

        ob_start();
        match ($jenis) {
            IMAGETYPE_JPEG => imagejpeg($gambar, null, 85),
            IMAGETYPE_PNG => imagepng($gambar, null, 6),
            default => imagewebp($gambar, null, 82),
        };
        $isi = (string) ob_get_clean();
        unset($gambar);

        $path = trim($folder, '/').'/'.Str::random(40).'.'.$ekstensi;
        Storage::disk('public')->put($path, $isi);

        return $path;
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
     * Aturan validasi gambar dari config/desa.php: jenis isi berkas (bukan ekstensi), ukuran, dan dimensi.
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
     * Pesan galat untuk aturanGambar(), dengan kunci berawalan nama kolom. Teks sama dengan uploader di frontend.
     *
     * @return array<string, string>
     */
    public static function pesanGambar(string $kolom, bool $persegi = false): array
    {
        $px = (int) config('desa.unggahan.gambar_maks_piksel');
        $terlalu = 'Ditolak: ukuran berkas melebihi '.self::formatMb(self::batasMb('gambar')).' MB. Pilih berkas lain.';

        return [
            "{$kolom}.file" => 'Berkas gambar tidak valid.',
            "{$kolom}.uploaded" => $terlalu,
            "{$kolom}.mimetypes" => 'Ditolak: hanya JPG, PNG, atau WebP yang diizinkan. Pilih berkas lain.',
            "{$kolom}.max" => $terlalu,
            "{$kolom}.dimensions" => $persegi
                ? "Gambar harus persegi (rasio 1:1) dan maksimal {$px} × {$px} piksel."
                : "Dimensi gambar maksimal {$px} × {$px} piksel.",
        ];
    }

    // ---- PDF (disk local, privat) ------------------------------------------------------------------

    /**
     * PDF disimpan di disk privat (`local`), bukan di web root; hanya bisa diunduh lewat rute yang memeriksa status terbit.
     */
    public static function simpanPdf(UploadedFile $berkas, string $folder): string
    {
        return Storage::disk('local')->putFileAs(trim($folder, '/'), $berkas, Str::random(40).'.pdf');
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
        $terlalu = 'Ditolak: ukuran berkas melebihi '.self::formatMb(self::batasMb('pdf')).' MB. Pilih berkas lain.';

        return [
            "{$kolom}.file" => 'Berkas tidak valid.',
            "{$kolom}.uploaded" => $terlalu,
            "{$kolom}.mimetypes" => 'Ditolak: hanya PDF yang diizinkan. Pilih berkas lain.',
            "{$kolom}.max" => $terlalu,
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

    // ---- Batas ukuran ------------------------------------------------------------------------------

    /**
     * Batas ukuran dalam MB (untuk uploader FE dan pesan galat).
     */
    public static function batasMb(string $jenis): float
    {
        return (int) config($jenis === 'pdf' ? 'desa.unggahan.pdf_maks_kb' : 'desa.unggahan.gambar_maks_kb') / 1024;
    }

    public static function formatMb(float $mb): string
    {
        return rtrim(rtrim(number_format($mb, 1, ',', '.'), '0'), ',');
    }

    // ---- Pembantu pemrosesan gambar ----------------------------------------------------------------

    private static function galat(string $kolom, string $pesan): ValidationException
    {
        return ValidationException::withMessages([$kolom => $pesan]);
    }

    /**
     * Mencegah kehabisan memori PHP saat membuka gambar sangat besar (estimasi 5 byte per piksel dua salinan).
     */
    private static function pastikanMemoriCukup(int $lebar, int $tinggi, string $kolom): void
    {
        $batas = self::byte((string) ini_get('memory_limit'));
        if ($batas <= 0) {
            return;
        }

        $perlu = $lebar * $tinggi * 5 * 2;
        if ($perlu + memory_get_usage() > $batas * 0.9) {
            throw self::galat($kolom, 'Gambar terlalu besar untuk diproses. Perkecil dimensinya lalu coba lagi.');
        }
    }

    private static function byte(string $nilai): int
    {
        $nilai = trim($nilai);
        if ($nilai === '' || $nilai === '-1') {
            return -1;
        }

        $angka = (int) $nilai;

        return match (strtolower(substr($nilai, -1))) {
            'g' => $angka * 1024 ** 3,
            'm' => $angka * 1024 ** 2,
            'k' => $angka * 1024,
            default => $angka,
        };
    }

    private static function putarSesuaiExif(GdImage $gambar, string $path): GdImage
    {
        if (! function_exists('exif_read_data')) {
            return $gambar;
        }

        $orientasi = (int) (@exif_read_data($path)['Orientation'] ?? 1);
        $sudut = match ($orientasi) {
            3 => 180,
            6 => -90,
            8 => 90,
            default => 0,
        };

        if ($sudut === 0) {
            return $gambar;
        }

        $putar = imagerotate($gambar, $sudut, 0);

        return $putar instanceof GdImage ? $putar : $gambar;
    }

    private static function kecilkan(GdImage $gambar): GdImage
    {
        $maks = (int) config('desa.unggahan.gambar_sisi_panjang');
        $lebar = imagesx($gambar);
        $tinggi = imagesy($gambar);

        if ($maks <= 0 || max($lebar, $tinggi) <= $maks) {
            return $gambar;
        }

        $skala = $maks / max($lebar, $tinggi);
        $kecil = imagescale($gambar, max(1, (int) round($lebar * $skala)), max(1, (int) round($tinggi * $skala)));

        return $kecil instanceof GdImage ? $kecil : $gambar;
    }
}
