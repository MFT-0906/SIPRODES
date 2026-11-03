<?php

namespace App\Enums;

/**
 * Modul admin yang dapat ditugaskan ke akun Staf (dipakai penuh di Fase 3).
 */
enum Modul: string
{
    case Berita = 'berita';
    case Potensi = 'potensi';
    case Profil = 'profil';
    case Apbdes = 'apbdes';
    case Aspirasi = 'aspirasi';
    case Akun = 'akun';

    public function label(): string
    {
        return match ($this) {
            self::Berita => 'Berita & Agenda',
            self::Potensi => 'Potensi (Pertanian, Kelompok Tani, UMKM)',
            self::Profil => 'Profil & Struktur Perangkat',
            self::Apbdes => 'APBDes (ringkasan & PDF)',
            self::Aspirasi => 'Aspirasi Warga',
            self::Akun => 'Akun & Hak Akses',
        };
    }
}
