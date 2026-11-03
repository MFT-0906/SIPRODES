<?php

namespace Database\Factories;

use App\Models\Dusun;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Dusun>
 */
class DusunFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => 'Dusun '.fake()->unique()->numberBetween(7, 999),
            'urutan' => fake()->numberBetween(1, 99),
        ];
    }
}
