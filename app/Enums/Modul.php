<?php

namespace App\Enums;

/**
 * Modul admin. Staf hanya boleh modul yang ditugaskan; Super Admin memegang semuanya.
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

    /**
     * Akun & Hak Akses hanya untuk Super Admin dan tidak dapat ditugaskan ke Staf.
     */
    public function dapatDitugaskanKeStaf(): bool
    {
        return $this !== self::Akun;
    }

    /**
     * @return list<self>
     */
    public static function untukStaf(): array
    {
        return array_values(array_filter(self::cases(), fn (self $modul) => $modul->dapatDitugaskanKeStaf()));
    }
}
