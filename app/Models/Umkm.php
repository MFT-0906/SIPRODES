<?php

namespace App\Models;

use App\Enums\StatusTerbit;
use App\Models\Concerns\DapatDicari;
use App\Models\Concerns\MemilikiStatusTerbit;
use Database\Factories\UmkmFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['nama', 'produk', 'deskripsi', 'foto_path', 'foto_alt', 'whatsapp', 'whatsapp_disetujui', 'status', 'contoh'])]
class Umkm extends Model
{
    /** @use HasFactory<UmkmFactory> */
    use DapatDicari, HasFactory, MemilikiStatusTerbit;

    protected $table = 'umkm';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'whatsapp_disetujui' => 'boolean',
            'status' => StatusTerbit::class,
            'contoh' => 'boolean',
        ];
    }

    /**
     * Nomor WhatsApp hanya boleh tampil di publik bila pemiliknya menyetujui.
     */
    public function whatsappTampil(): ?string
    {
        return $this->whatsapp_disetujui ? $this->whatsapp : null;
    }
}
