<?php

namespace App\Support;

use App\Enums\Modul;
use App\Models\ApbdesPeriode;
use App\Models\Berita;
use App\Models\Komoditas;
use App\Models\PerangkatDesa;
use App\Models\ProfilDesa;
use App\Models\Umkm;

/**
 * Daftar periksa kelengkapan data resmi situs untuk kartu di dashboard. Setiap butir mengarah ke
 * halaman pengisiannya dan hanya ditampilkan kepada akun yang boleh membuka modul terkait.
 * Data contoh (kolom `contoh`) tidak dihitung sebagai data resmi.
 */
final class KelengkapanSitus
{
    /**
     * @return list<array{kunci: string, label: string, keterangan: string, selesai: bool, href: string, modul: Modul}>
     */
    public static function daftar(): array
    {
        $profil = ProfilDesa::query()->first();
        $kontakAda = $profil && (filled($profil->telepon) || filled($profil->email));

        return [
            [
                'kunci' => 'kontak',
                'label' => 'Kontak resmi desa',
                'keterangan' => 'Telepon/email dan jam layanan, lalu tandai sudah diverifikasi.',
                'selesai' => (bool) ($kontakAda && $profil->kontak_terverifikasi && filled($profil->jam_layanan)),
                'href' => route('admin.profil'),
                'modul' => Modul::Profil,
            ],
            [
                'kunci' => 'visi_misi',
                'label' => 'Visi & misi resmi',
                'keterangan' => 'Naskah resmi dari desa, tandai sebagai resmi.',
                'selesai' => (bool) ($profil && filled($profil->visi) && $profil->visi_misi_resmi),
                'href' => route('admin.profil'),
                'modul' => Modul::Profil,
            ],
            [
                'kunci' => 'data_desa',
                'label' => 'Angka desa diverifikasi',
                'keterangan' => 'Penduduk, KK, luas, dan dusun dikonfirmasi desa.',
                'selesai' => (bool) ($profil && $profil->data_terverifikasi),
                'href' => route('admin.profil'),
                'modul' => Modul::Profil,
            ],
            [
                'kunci' => 'perangkat',
                'label' => 'Struktur perangkat desa',
                'keterangan' => 'Nama dan jabatan perangkat yang sebenarnya.',
                'selesai' => (bool) ($profil && $profil->perangkat_resmi)
                    && PerangkatDesa::query()->where('contoh', false)->exists(),
                'href' => route('admin.struktur'),
                'modul' => Modul::Profil,
            ],
            [
                'kunci' => 'apbdes',
                'label' => 'APBDes diterbitkan',
                'keterangan' => 'Ringkasan anggaran dan dokumen PDF tahun berjalan.',
                'selesai' => ApbdesPeriode::query()->terbit()->where('contoh', false)->exists(),
                'href' => route('admin.apbdes'),
                'modul' => Modul::Apbdes,
            ],
            [
                'kunci' => 'potensi',
                'label' => 'Potensi desa',
                'keterangan' => 'Minimal satu komoditas pertanian atau UMKM terbit.',
                'selesai' => Komoditas::query()->terbit()->where('contoh', false)->exists()
                    || Umkm::query()->terbit()->where('contoh', false)->exists(),
                'href' => route('admin.pertanian'),
                'modul' => Modul::Potensi,
            ],
            [
                'kunci' => 'berita',
                'label' => 'Berita pertama',
                'keterangan' => 'Kabar desa pertama, misalnya peluncuran website.',
                'selesai' => Berita::query()->terbit()->where('contoh', false)->exists(),
                'href' => route('admin.berita.create'),
                'modul' => Modul::Berita,
            ],
        ];
    }
}
