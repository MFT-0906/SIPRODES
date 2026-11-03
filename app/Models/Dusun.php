<?php

namespace App\Models;

use Database\Factories\DusunFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nama', 'urutan'])]
class Dusun extends Model
{
    /** @use HasFactory<DusunFactory> */
    use HasFactory;

    protected $table = 'dusun';

    /**
     * @return HasMany<KelompokTani, $this>
     */
    public function kelompokTani(): HasMany
    {
        return $this->hasMany(KelompokTani::class);
    }
}
