<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Bawaan (data awal) koleksi demo untuk modul yang belum memakai database (Aspirasi).
 * Isinya mengikuti contoh di desain Figma; bukan data resmi desa.
 */
class DemoStoreKoleksi
{
    // TODO(backend): hapus; Aspirasi disambungkan di Fase 9.

    /** @return list<array<string, mixed>> */
    public static function bawaan(string $kunci): array
    {
        return match ($kunci) {
            'aspirasi' => self::aspirasi(),
            default => [],
        };
    }

    /**
     * Aspirasi contoh dari desain. NIK tidak diisi pengirim pada semua contoh; bila ada, hanya tampil disamarkan.
     *
     * @return list<array<string, mixed>>
     */
    private static function aspirasi(): array
    {
        $a = fn (string $id, string $nama, string $pesan, string $waktu, string $status, ?string $catatan = null) => [
            'id' => $id, 'nama' => $nama, 'pesan' => $pesan, 'diterima' => $waktu, 'status' => $status, 'nik' => null,
            'catatan' => $catatan,
            'riwayat' => [['waktu' => $waktu, 'teks' => 'Aspirasi diterima (status: Baru)']],
        ];

        return [
            $a('asp-1', 'Pengirim A', 'Mohon informasi tentang perbaikan jalan dusun yang rusak setelah hujan. Pesan contoh dari warga: isi aspirasi asli hanya terlihat oleh perangkat desa yang berwenang dan tidak pernah ditampilkan di situs publik.', '2026-10-27 12:40', 'baru'),
            $a('asp-2', 'Pengirim B', 'Mohon informasi tentang jadwal posyandu bulan depan.', '2026-10-26 09:15', 'baru'),
            $a('asp-3', 'Pengirim C', 'Mohon informasi tentang bantuan bibit jagung.', '2026-10-25 16:02', 'baru'),
            $a('asp-4', 'Pengirim D', 'Mohon informasi tentang lampu jalan yang padam.', '2026-10-20 19:30', 'dibaca'),
            $a('asp-5', 'Pengirim E', 'Mohon informasi tentang surat pengantar.', '2026-10-18 08:10', 'selesai'),
        ];
    }

    public static function idBaru(string $kunci): string
    {
        return $kunci.'-'.Str::lower(Str::random(6));
    }
}
