<?php

namespace Database\Factories;

use App\Models\Aspirasi;
use App\Models\AspirasiRiwayat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AspirasiRiwayat>
 */
class AspirasiRiwayatFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'aspirasi_id' => Aspirasi::factory(),
            'user_id' => null,
            'aksi' => 'diterima',
            'catatan' => null,
        ];
    }
}
