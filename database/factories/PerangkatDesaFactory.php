<?php

namespace Database\Factories;

use App\Models\Jabatan;
use App\Models\PerangkatDesa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PerangkatDesa>
 */
class PerangkatDesaFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => fake()->name(),
            'jabatan_id' => Jabatan::factory(),
            'urutan' => fake()->numberBetween(1, 99),
            'tampil' => true,
            'contoh' => false,
        ];
    }
}
