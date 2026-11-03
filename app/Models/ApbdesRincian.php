<?php

namespace App\Models;

use App\Enums\JenisAnggaran;
use Database\Factories\ApbdesRincianFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['periode_id', 'jenis', 'uraian', 'anggaran', 'urutan'])]
class ApbdesRincian extends Model
{
    /** @use HasFactory<ApbdesRincianFactory> */
    use HasFactory;

    protected $table = 'apbdes_rincian';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['jenis' => JenisAnggaran::class, 'anggaran' => 'integer'];
    }

    /**
     * @return BelongsTo<ApbdesPeriode, $this>
     */
    public function periode(): BelongsTo
    {
        return $this->belongsTo(ApbdesPeriode::class, 'periode_id');
    }
}
