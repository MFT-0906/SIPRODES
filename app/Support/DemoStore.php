<?php

namespace App\Support;

/**
 * Penyimpanan sementara untuk meninjau alur admin (tambah, ubah, hapus) tanpa database.
 * Perubahan disimpan di sesi pengguna dan tidak memengaruhi situs publik.
 * Berita, Agenda, dan Akun sudah memakai database; sisanya dihapus bertahap sampai Fase 10.
 */
class DemoStore
{
    // TODO(backend): hapus kelas ini; simpan, ubah, dan hapus lewat model dan database.

    /**
     * Koleksi demo generik untuk modul admin (struktur, apbdes, komoditas, kelompok, umkm, aspirasi).
     *
     * @return list<array<string, mixed>>
     */
    public static function daftar(string $kunci): array
    {
        return array_values(session('demo.'.$kunci, DemoStoreKoleksi::bawaan($kunci)));
    }

    /** @return array<string, mixed>|null */
    public static function item(string $kunci, ?string $id): ?array
    {
        return $id === null ? null : collect(self::daftar($kunci))->firstWhere('id', $id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function simpan(string $kunci, array $data, ?string $id = null): string
    {
        $daftar = self::daftar($kunci);
        $id ??= DemoStoreKoleksi::idBaru($kunci);
        $data = ['id' => $id] + $data;

        $ditemukan = false;
        foreach ($daftar as $i => $item) {
            if ($item['id'] === $id) {
                $daftar[$i] = $data + $item;
                $ditemukan = true;
            }
        }
        if (! $ditemukan) {
            $daftar[] = $data;
        }

        session(['demo.'.$kunci => $daftar]);

        return $id;
    }

    public static function hapus(string $kunci, string $id): void
    {
        session(['demo.'.$kunci => array_values(array_filter(self::daftar($kunci), fn (array $i) => $i['id'] !== $id))]);
    }

    /** Menggeser item satu langkah ke atas (-1) atau ke bawah (1) dalam urutan. */
    public static function geser(string $kunci, string $id, int $arah): void
    {
        $daftar = self::daftar($kunci);
        $posisi = array_search($id, array_column($daftar, 'id'), true);
        $tujuan = $posisi === false ? false : $posisi + $arah;

        if ($tujuan === false || $tujuan < 0 || $tujuan >= count($daftar)) {
            return;
        }

        [$daftar[$posisi], $daftar[$tujuan]] = [$daftar[$tujuan], $daftar[$posisi]];
        session(['demo.'.$kunci => $daftar]);
    }
}
