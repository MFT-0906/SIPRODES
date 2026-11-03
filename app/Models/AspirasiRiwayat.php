<?php

namespace App\Models;

use Database\Factories\AspirasiRiwayatFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['aspirasi_id', 'user_id', 'aksi', 'catatan'])]
class AspirasiRiwayat extends Model
{
    /** @use HasFactory<AspirasiRiwayatFactory> */
    use HasFactory;

    protected $table = 'aspirasi_riwayat';

    /**
     * @return BelongsTo<Aspirasi, $this>
     */
    public function aspirasi(): BelongsTo
    {
        return $this->belongsTo(Aspirasi::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
