<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Penyimpanan sementara untuk meninjau alur admin (tambah, ubah, hapus) tanpa database.
 * Perubahan disimpan di sesi pengguna dan tidak memengaruhi situs publik.
 */
class DemoStore
{
    // TODO(backend): hapus kelas ini; simpan, ubah, dan hapus lewat model dan database.

    private const BERITA = 'demo.berita';

    private const AGENDA = 'demo.agenda';

    /** @return list<array<string, mixed>> */
    public static function berita(): array
    {
        return array_values(session(self::BERITA, DemoContent::berita()));
    }

    /** @return array<string, mixed>|null */
    public static function beritaSlug(string $slug): ?array
    {
        return collect(self::berita())->firstWhere('slug', $slug);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function simpanBerita(array $data, ?string $slugLama = null): string
    {
        $daftar = self::berita();
        $slug = $slugLama ?? self::slugUnik(Str::slug($data['title']), array_column($daftar, 'slug'));
        $data = ['slug' => $slug, 'penulis' => 'Admin desa', 'isi' => [], 'diubah' => now()->toDateString()] + $data;

        $ditemukan = false;
        foreach ($daftar as $i => $item) {
            if ($item['slug'] === $slug) {
                $daftar[$i] = $data + $item;
                $ditemukan = true;
            }
        }
        if (! $ditemukan) {
            array_unshift($daftar, $data);
        }

        session([self::BERITA => $daftar]);

        return $slug;
    }

    public static function hapusBerita(string $slug): void
    {
        session([self::BERITA => array_values(array_filter(self::berita(), fn (array $b) => $b['slug'] !== $slug))]);
    }

    /**
     * Isi berita sebagai teks untuk editor: memakai `isi_teks` bila ada, atau mengubah blok contoh
     * (p, h2, kutipan) menjadi teks dengan penanda ringan.
     *
     * @param  array<string, mixed>  $item
     */
    public static function isiTeks(array $item): string
    {
        if (isset($item['isi_teks'])) {
            return $item['isi_teks'];
        }

        return collect($item['isi'] ?? [])
            ->map(fn (array $blok) => match ($blok[0]) {
                'h2' => '## '.$blok[1],
                'kutipan' => '> '.$blok[1],
                default => $blok[1],
            })
            ->implode("\n\n");
    }

    /** @return list<array<string, mixed>> */
    public static function agenda(): array
    {
        return array_values(session(self::AGENDA, DemoContent::agenda()));
    }

    /** @return array<string, mixed>|null */
    public static function agendaId(string $id): ?array
    {
        return collect(self::agenda())->firstWhere('id', $id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function simpanAgenda(array $data, ?string $id = null): string
    {
        $daftar = self::agenda();
        $id ??= 'agenda-'.Str::lower(Str::random(6));
        $data = ['id' => $id, 'diubah' => now()->toDateString()] + $data;
        $data['time'] = $data['waktu'] ? str_replace(':', '.', $data['waktu']).' WIB' : 'Waktu menyusul';

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

        session([self::AGENDA => $daftar]);

        return $id;
    }

    public static function hapusAgenda(string $id): void
    {
        session([self::AGENDA => array_values(array_filter(self::agenda(), fn (array $a) => $a['id'] !== $id))]);
    }

    /**
     * Koleksi demo generik untuk modul admin (struktur, apbdes, komoditas, kelompok, umkm, aspirasi, akun).
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

    /** @param  list<string>  $dipakai */
    private static function slugUnik(string $dasar, array $dipakai): string
    {
        $dasar = $dasar !== '' ? $dasar : 'berita';
        $slug = $dasar;
        for ($n = 2; in_array($slug, $dipakai, true); $n++) {
            $slug = $dasar.'-'.$n;
        }

        return $slug;
    }
}
