<?php

namespace App\Models;

use App\Enums\StatusTerbit;
use App\Models\Concerns\DapatDicari;
use App\Models\Concerns\MemilikiStatusTerbit;
use Database\Factories\BeritaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'judul', 'slug', 'kategori', 'ringkasan', 'isi', 'gambar_path', 'gambar_alt', 'tanggal',
    'status', 'penulis_id', 'diterbitkan_at', 'contoh',
])]
class Berita extends Model
{
    /** @use HasFactory<BeritaFactory> */
    use DapatDicari, HasFactory, MemilikiStatusTerbit;

    protected $table = 'berita';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'status' => StatusTerbit::class,
            'diterbitkan_at' => 'datetime',
            'contoh' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function penulis(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penulis_id');
    }
}
