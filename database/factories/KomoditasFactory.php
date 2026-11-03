<?php

namespace Database\Factories;

use App\Enums\StatusTerbit;
use App\Models\Komoditas;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Komoditas>
 */
class KomoditasFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => fake()->unique()->words(2, true),
            'deskripsi' => fake()->paragraph(),
            'musim_panen' => null,
            'unggulan' => false,
            'status' => StatusTerbit::Terbit,
            'contoh' => false,
        ];
    }

    public function draf(): static
    {
        return $this->state(fn (array $attributes) => ['status' => StatusTerbit::Draf]);
    }
}
