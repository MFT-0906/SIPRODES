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
}
