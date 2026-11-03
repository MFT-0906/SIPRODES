<?php

namespace App\Models;

use Database\Factories\ApbdesDokumenFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['periode_id', 'judul', 'keterangan_periode', 'path', 'ukuran', 'diunggah_oleh'])]
class ApbdesDokumen extends Model
{
    /** @use HasFactory<ApbdesDokumenFactory> */
    use HasFactory;

    protected $table = 'apbdes_dokumen';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['ukuran' => 'integer'];
    }

    /**
     * @return BelongsTo<ApbdesPeriode, $this>
     */
    public function periode(): BelongsTo
    {
        return $this->belongsTo(ApbdesPeriode::class, 'periode_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function pengunggah(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diunggah_oleh');
    }
}
