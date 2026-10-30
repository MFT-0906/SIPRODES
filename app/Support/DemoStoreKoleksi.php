<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Bawaan (data awal) koleksi demo untuk modul admin fase 10. Isinya mengikuti contoh di desain Figma
 * dan data placeholder publik; bukan data resmi desa.
 */
class DemoStoreKoleksi
{
    // TODO(backend): hapus; data berasal dari database.

    /** @return list<array<string, mixed>> */
    public static function bawaan(string $kunci): array
    {
        return match ($kunci) {
            'struktur' => self::struktur(),
            'apbdes' => self::apbdes(),
            'komoditas' => self::komoditas(),
            'kelompok' => self::kelompok(),
            'umkm' => self::umkm(),
            'aspirasi' => self::aspirasi(),
            'akun' => self::akun(),
            default => [],
        };
    }

    /** @return list<array<string, mixed>> */
    private static function struktur(): array
    {
        return collect(DemoContent::profil()['perangkat']['daftar'])
            ->values()
            ->map(fn (array $p, int $i) => [
                'id' => 'perangkat-'.($i + 1), 'nama' => $p['name'], 'jabatan' => $p['position'], 'tampil' => true,
            ])
            ->all();
    }

    /** @return list<array<string, mixed>> */
    private static function apbdes(): array
    {
        $data = DemoContent::apbdes();

        return collect($data['tahun'])
            ->map(fn (array $t, int $tahun) => [
                'id' => (string) $tahun, 'tahun' => $tahun, 'periode' => 'Jan – Des', 'pendapatan' => null, 'belanja' => null,
                'dokumen' => $t['dokumen'], 'status' => 'terbit',
            ])
            ->values()
            ->all();
    }

    /** @return list<array<string, mixed>> */
    private static function komoditas(): array
    {
        return collect(DemoContent::potensi()['komoditas'])
            ->values()
            ->map(fn (array $k, int $i) => [
                'id' => 'komoditas-'.($i + 1), 'nama' => $k['nama'], 'musim_panen' => $k['musim_panen'],
                'status' => 'terbit', 'deskripsi' => $k['deskripsi'],
            ])
            ->all();
    }

    /** @return list<array<string, mixed>> */
    private static function kelompok(): array
    {
        return collect(DemoContent::potensi()['kelompok_tani']['daftar'])
            ->values()
            ->map(fn (array $k, int $i) => [
                'id' => 'kelompok-'.($i + 1), 'nama' => $k['nama'], 'komoditas' => $k['komoditas'], 'dusun' => $k['dusun'],
                'ketua' => null, 'kontak' => $k['kontak'], 'disetujui' => $k['kontak_disetujui'],
            ])
            ->all();
    }

    /** @return list<array<string, mixed>> */
    private static function umkm(): array
    {
        return collect(DemoContent::potensi()['umkm']['daftar'])
            ->values()
            ->map(fn (array $u, int $i) => [
                'id' => 'umkm-'.($i + 1), 'nama' => $u['name'], 'produk' => $u['products'], 'deskripsi' => $u['description'],
                'whatsapp' => $u['whatsapp'], 'disetujui' => $u['whatsappApproved'], 'status' => 'terbit',
            ])
            ->all();
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

    /** @return list<array<string, mixed>> */
    private static function akun(): array
    {
        return [
            ['id' => 'akun-1', 'nama' => 'Nama Admin 1', 'email' => 'admin1@…', 'peran' => 'super_admin', 'aktif' => true],
            ['id' => 'akun-2', 'nama' => 'Nama Admin 2', 'email' => 'staf1@…', 'peran' => 'staf', 'aktif' => true],
            ['id' => 'akun-3', 'nama' => 'Nama Admin 3', 'email' => 'staf2@…', 'peran' => 'staf', 'aktif' => false],
        ];
    }

    public static function idBaru(string $kunci): string
    {
        return $kunci.'-'.Str::lower(Str::random(6));
    }
}
