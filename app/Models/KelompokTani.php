<?php

namespace App\Models;

use App\Models\Concerns\DapatDicari;
use Database\Factories\KelompokTaniFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['nama', 'komoditas_id', 'dusun_id', 'ketua', 'kontak', 'kontak_disetujui', 'contoh'])]
class KelompokTani extends Model
{
    /** @use HasFactory<KelompokTaniFactory> */
    use DapatDicari, HasFactory;

    protected $table = 'kelompok_tani';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['kontak_disetujui' => 'boolean', 'contoh' => 'boolean'];
    }

    /**
     * @return BelongsTo<Komoditas, $this>
     */
    public function komoditas(): BelongsTo
    {
        return $this->belongsTo(Komoditas::class);
    }

    /**
     * @return BelongsTo<Dusun, $this>
     */
    public function dusun(): BelongsTo
    {
        return $this->belongsTo(Dusun::class);
    }

    /**
     * Kontak hanya boleh tampil di publik bila sudah disetujui.
     */
    public function kontakTampil(): ?string
    {
        return $this->kontak_disetujui ? $this->kontak : null;
    }
}
