<?php

namespace App\Models;

use App\Enums\StatusTerbit;
use App\Models\Concerns\MemilikiStatusTerbit;
use Database\Factories\AgendaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['judul', 'tanggal', 'waktu', 'lokasi', 'deskripsi', 'status', 'contoh'])]
class Agenda extends Model
{
    /** @use HasFactory<AgendaFactory> */
    use HasFactory, MemilikiStatusTerbit;

    protected $table = 'agenda';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'status' => StatusTerbit::class,
            'contoh' => 'boolean',
        ];
    }

    /**
     * @param  Builder<static>  $query
     */
    public function scopeMendatang(Builder $query): void
    {
        $query->whereDate('tanggal', '>=', today());
    }

    /**
     * @param  Builder<static>  $query
     */
    public function scopeLewat(Builder $query): void
    {
        $query->whereDate('tanggal', '<', today());
    }
}
