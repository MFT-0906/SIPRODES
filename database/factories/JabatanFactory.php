<?php

namespace Database\Factories;

use App\Models\Jabatan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Jabatan>
 */
class JabatanFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => 'Jabatan '.fake()->unique()->numberBetween(1, 9999),
            'urutan' => fake()->numberBetween(1, 99),
        ];
    }
}
