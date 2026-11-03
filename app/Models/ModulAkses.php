<?php

namespace App\Models;

use App\Enums\Modul;
use Database\Factories\ModulAksesFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'modul'])]
class ModulAkses extends Model
{
    /** @use HasFactory<ModulAksesFactory> */
    use HasFactory;

    protected $table = 'modul_akses';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['modul' => Modul::class];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
