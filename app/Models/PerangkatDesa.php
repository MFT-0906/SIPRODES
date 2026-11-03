<?php

namespace App\Models;

use Database\Factories\PerangkatDesaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['nama', 'jabatan_id', 'foto_path', 'urutan', 'tampil', 'contoh'])]
class PerangkatDesa extends Model
{
    /** @use HasFactory<PerangkatDesaFactory> */
    use HasFactory;

    protected $table = 'perangkat_desa';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['tampil' => 'boolean', 'contoh' => 'boolean'];
    }

    /**
     * @return BelongsTo<Jabatan, $this>
     */
    public function jabatan(): BelongsTo
    {
        return $this->belongsTo(Jabatan::class);
    }

    /**
     * @param  Builder<static>  $query
     */
    public function scopeTampil(Builder $query): void
    {
        $query->where('tampil', true);
    }
}
