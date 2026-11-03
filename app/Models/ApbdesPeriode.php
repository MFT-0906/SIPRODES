<?php

namespace App\Models;

use App\Enums\JenisAnggaran;
use App\Enums\StatusTerbit;
use App\Models\Concerns\MemilikiStatusTerbit;
use Database\Factories\ApbdesPeriodeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['tahun', 'periode', 'status', 'diterbitkan_at', 'contoh'])]
class ApbdesPeriode extends Model
{
    /** @use HasFactory<ApbdesPeriodeFactory> */
    use HasFactory, MemilikiStatusTerbit;

    protected $table = 'apbdes_periode';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => StatusTerbit::class,
            'diterbitkan_at' => 'datetime',
            'contoh' => 'boolean',
        ];
    }

    /**
     * @return HasMany<ApbdesRincian, $this>
     */
    public function rincian(): HasMany
    {
        return $this->hasMany(ApbdesRincian::class, 'periode_id')->orderBy('urutan');
    }

    /**
     * @return HasMany<ApbdesDokumen, $this>
     */
    public function dokumen(): HasMany
    {
        return $this->hasMany(ApbdesDokumen::class, 'periode_id');
    }

    /**
     * Total dihitung dari rincian, bukan disimpan. Null bila belum ada angka terisi.
     */
    public function total(JenisAnggaran $jenis): ?int
    {
        $baris = $this->rincian->where('jenis', $jenis)->whereNotNull('anggaran');

        return $baris->isEmpty() ? null : (int) $baris->sum('anggaran');
    }
}
