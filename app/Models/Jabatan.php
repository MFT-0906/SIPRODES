<?php

namespace App\Models;

use Database\Factories\JabatanFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nama', 'urutan'])]
class Jabatan extends Model
{
    /** @use HasFactory<JabatanFactory> */
    use HasFactory;

    protected $table = 'jabatan';

    /**
     * @return HasMany<PerangkatDesa, $this>
     */
    public function perangkat(): HasMany
    {
        return $this->hasMany(PerangkatDesa::class);
    }
}
