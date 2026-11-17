<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * Agregat kunjungan harian per halaman (tanpa data pribadi). Baris dengan path SEMUA berisi total seluruh situs.
 */
#[Fillable(['tanggal', 'path', 'jumlah_tayang', 'jumlah_pengunjung'])]
class StatistikKunjungan extends Model
{
    /** Path khusus untuk total seluruh situs pada satu hari. */
    public const SEMUA = '*';

    protected $table = 'statistik_kunjungan';

    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            // Format penyimpanan Y-m-d, sama dengan yang ditulis CatatKunjungan, agar pencocokan tanggal konsisten di semua basis data.
            'tanggal' => 'date:Y-m-d',
            'jumlah_tayang' => 'integer',
            'jumlah_pengunjung' => 'integer',
        ];
    }
}
