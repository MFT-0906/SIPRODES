<?php

namespace App\Models;

use App\Enums\StatusAspirasi;
use App\Models\Concerns\DapatDicari;
use Database\Factories\AspirasiFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Aspirasi warga. Tidak pernah ditampilkan di situs publik; NIK terenkripsi.
 */
#[Fillable(['nama', 'nik', 'pesan', 'status', 'dibaca_at', 'selesai_at', 'dianonimkan_at', 'contoh'])]
#[Hidden(['nik'])]
class Aspirasi extends Model
{
    /** @use HasFactory<AspirasiFactory> */
    use DapatDicari, HasFactory;

    protected $table = 'aspirasi';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'nik' => 'encrypted',
            'status' => StatusAspirasi::class,
            'dibaca_at' => 'datetime',
            'selesai_at' => 'datetime',
            'dianonimkan_at' => 'datetime',
            'contoh' => 'boolean',
        ];
    }

    /**
     * @return HasMany<AspirasiRiwayat, $this>
     */
    public function riwayat(): HasMany
    {
        return $this->hasMany(AspirasiRiwayat::class)->latest();
    }
}
