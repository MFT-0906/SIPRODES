<?php

namespace Database\Factories;

use App\Models\ApbdesDokumen;
use App\Models\ApbdesPeriode;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ApbdesDokumen>
 */
class ApbdesDokumenFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'periode_id' => ApbdesPeriode::factory(),
            'judul' => fake()->sentence(5),
            'keterangan_periode' => null,
            'path' => null,
            'ukuran' => null,
            'diunggah_oleh' => null,
        ];
    }
}
