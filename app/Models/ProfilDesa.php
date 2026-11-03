<?php

namespace App\Models;

use Database\Factories\ProfilDesaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Satu baris berisi profil desa. Gunakan ProfilDesa::saatIni().
 */
#[Fillable([
    'sejarah', 'visi', 'misi', 'visi_misi_resmi', 'alamat_wilayah', 'alamat', 'lintang', 'bujur',
    'telepon', 'email', 'jam_layanan', 'kontak_terverifikasi', 'penduduk', 'kk', 'luas_ha',
    'jumlah_dusun', 'mata_pencaharian', 'sumber_data', 'data_terverifikasi', 'perangkat_resmi',
])]
class ProfilDesa extends Model
{
    /** @use HasFactory<ProfilDesaFactory> */
    use HasFactory;

    protected $table = 'profil_desa';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'misi' => 'array',
            'visi_misi_resmi' => 'boolean',
            'kontak_terverifikasi' => 'boolean',
            'data_terverifikasi' => 'boolean',
            'perangkat_resmi' => 'boolean',
            'lintang' => 'decimal:7',
            'bujur' => 'decimal:7',
            'luas_ha' => 'decimal:2',
        ];
    }

    public static function saatIni(): self
    {
        return self::query()->firstOrCreate([]);
    }
}
