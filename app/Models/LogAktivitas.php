<?php

namespace App\Models;

use Database\Factories\LogAktivitasFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable(['user_id', 'aksi', 'subjek_type', 'subjek_id', 'keterangan', 'ip'])]
class LogAktivitas extends Model
{
    /** @use HasFactory<LogAktivitasFactory> */
    use HasFactory;

    protected $table = 'log_aktivitas';

    public const UPDATED_AT = null;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function subjek(): MorphTo
    {
        return $this->morphTo();
    }
}
