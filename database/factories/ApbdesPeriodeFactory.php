<?php

namespace Database\Factories;

use App\Enums\StatusTerbit;
use App\Models\ApbdesPeriode;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ApbdesPeriode>
 */
class ApbdesPeriodeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tahun = fake()->unique()->numberBetween(2000, 2099);

        return [
            'tahun' => $tahun,
            'periode' => "1 Januari – 31 Desember {$tahun}",
            'status' => StatusTerbit::Terbit,
            'diterbitkan_at' => now(),
            'contoh' => false,
        ];
    }

    public function draf(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => StatusTerbit::Draf,
            'diterbitkan_at' => null,
        ]);
    }
}
