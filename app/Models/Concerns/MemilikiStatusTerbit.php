<?php

namespace App\Models\Concerns;

use App\Enums\StatusTerbit;
use Illuminate\Database\Eloquent\Builder;

/**
 * Untuk model dengan kolom `status` (StatusTerbit). Publik hanya boleh membaca lewat scope terbit().
 */
trait MemilikiStatusTerbit
{
    /**
     * @param  Builder<static>  $query
     */
    public function scopeTerbit(Builder $query): void
    {
        $query->where($query->qualifyColumn('status'), StatusTerbit::Terbit->value);
    }

    /**
     * @param  Builder<static>  $query
     */
    public function scopeDraf(Builder $query): void
    {
        $query->where($query->qualifyColumn('status'), StatusTerbit::Draf->value);
    }
}
