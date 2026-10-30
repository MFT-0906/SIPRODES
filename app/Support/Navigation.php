<?php

namespace App\Support;

/**
 * Susunan menu situs publik. Label desktop lebih ringkas daripada label menu HP (mengikuti Figma).
 */
class Navigation
{
    /**
     * @return list<array{label: string, mobile: string, route: string, match: list<string>, icon: string}>
     */
    public static function publik(): array
    {
        return [
            ['label' => 'Beranda', 'mobile' => 'Beranda', 'route' => 'home', 'match' => ['home'], 'icon' => 'home'],
            ['label' => 'Profil Desa', 'mobile' => 'Profil Desa', 'route' => 'profil', 'match' => ['profil'], 'icon' => 'building'],
            ['label' => 'APBDes', 'mobile' => 'Transparansi APBDes', 'route' => 'apbdes', 'match' => ['apbdes'], 'icon' => 'wallet'],
            ['label' => 'Potensi Desa', 'mobile' => 'Potensi Desa', 'route' => 'potensi', 'match' => ['potensi'], 'icon' => 'sprout'],
            ['label' => 'Berita & Agenda', 'mobile' => 'Berita & Agenda', 'route' => 'berita.index', 'match' => ['berita.*'], 'icon' => 'newspaper'],
            ['label' => 'Kontak', 'mobile' => 'Kontak & Lokasi', 'route' => 'kontak', 'match' => ['kontak'], 'icon' => 'map-pin'],
        ];
    }

    /**
     * Tautan "Tautan Penting" di footer.
     *
     * @return list<array{label: string, route: string}>
     */
    public static function footer(): array
    {
        return [
            ['label' => 'Profil Desa', 'route' => 'profil'],
            ['label' => 'Transparansi APBDes', 'route' => 'apbdes'],
            ['label' => 'Potensi Desa', 'route' => 'potensi'],
            ['label' => 'Berita & Agenda', 'route' => 'berita.index'],
            ['label' => 'Ruang Aspirasi', 'route' => 'aspirasi.create'],
        ];
    }

    /**
     * Menu sidebar admin per grup (mengikuti Figma `17:78`). Item bertanda `super_admin` hanya untuk Super Admin.
     * Ini hanya penyaring tampilan; izin sebenarnya wajib ditegakkan backend.
     *
     * @return list<array{label: string, super_admin?: bool, items: list<array{label: string, route: string, match: list<string>, icon: string, badge?: string}>}>
     */
    public static function admin(): array
    {
        return [
            ['label' => 'Utama', 'items' => [
                ['label' => 'Ringkasan', 'route' => 'admin.dashboard', 'match' => ['admin.dashboard'], 'icon' => 'dashboard'],
            ]],
            ['label' => 'Konten Desa', 'items' => [
                ['label' => 'Profil Desa', 'route' => 'admin.profil', 'match' => ['admin.profil'], 'icon' => 'building'],
                ['label' => 'Struktur Perangkat', 'route' => 'admin.struktur', 'match' => ['admin.struktur'], 'icon' => 'users'],
                ['label' => 'APBDes', 'route' => 'admin.apbdes', 'match' => ['admin.apbdes'], 'icon' => 'wallet'],
            ]],
            ['label' => 'Potensi', 'items' => [
                ['label' => 'Potensi Pertanian', 'route' => 'admin.pertanian', 'match' => ['admin.pertanian'], 'icon' => 'sprout'],
                ['label' => 'Kelompok Tani', 'route' => 'admin.kelompok-tani', 'match' => ['admin.kelompok-tani'], 'icon' => 'users'],
                ['label' => 'UMKM', 'route' => 'admin.umkm', 'match' => ['admin.umkm'], 'icon' => 'store'],
            ]],
            ['label' => 'Publikasi', 'items' => [
                ['label' => 'Berita', 'route' => 'admin.berita.index', 'match' => ['admin.berita.*'], 'icon' => 'newspaper'],
                ['label' => 'Agenda', 'route' => 'admin.agenda.index', 'match' => ['admin.agenda.*'], 'icon' => 'calendar'],
            ]],
            ['label' => 'Layanan', 'items' => [
                ['label' => 'Aspirasi Warga', 'route' => 'admin.aspirasi.index', 'match' => ['admin.aspirasi.*'], 'icon' => 'message', 'badge' => 'aspirasi_baru'],
            ]],
            ['label' => 'Administrasi · Super Admin', 'super_admin' => true, 'items' => [
                ['label' => 'Akun & Hak Akses', 'route' => 'admin.akun.index', 'match' => ['admin.akun.*'], 'icon' => 'shield'],
            ]],
        ];
    }
}
