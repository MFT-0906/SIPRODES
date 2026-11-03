<?php

namespace Database\Factories;

use App\Enums\JenisAnggaran;
use App\Models\ApbdesPeriode;
use App\Models\ApbdesRincian;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ApbdesRincian>
 */
class ApbdesRincianFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'periode_id' => ApbdesPeriode::factory(),
            'jenis' => JenisAnggaran::Pendapatan,
            'uraian' => fake()->sentence(3),
            'anggaran' => fake()->numberBetween(1, 500) * 1_000_000,
            'urutan' => 0,
        ];
    }
}
