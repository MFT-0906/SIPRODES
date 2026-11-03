<?php

namespace App\Models;

use App\Enums\StatusTerbit;
use App\Models\Concerns\MemilikiStatusTerbit;
use Database\Factories\KomoditasFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nama', 'deskripsi', 'musim_panen', 'foto_path', 'foto_alt', 'unggulan', 'status', 'contoh'])]
class Komoditas extends Model
{
    /** @use HasFactory<KomoditasFactory> */
    use HasFactory, MemilikiStatusTerbit;

    protected $table = 'komoditas';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'unggulan' => 'boolean',
            'status' => StatusTerbit::class,
            'contoh' => 'boolean',
        ];
    }

    /**
     * @return HasMany<KelompokTani, $this>
     */
    public function kelompokTani(): HasMany
    {
        return $this->hasMany(KelompokTani::class);
    }
}
