<?php

namespace Database\Factories;

use App\Models\KelompokTani;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KelompokTani>
 */
class KelompokTaniFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => 'Kelompok Tani '.fake()->unique()->numberBetween(1, 9999),
            'komoditas_id' => null,
            'dusun_id' => null,
            'ketua' => fake()->name(),
            'kontak' => null,
            'kontak_disetujui' => false,
            'contoh' => false,
        ];
    }

    public function kontakDisetujui(): static
    {
        return $this->state(fn (array $attributes) => [
            'kontak' => '081200000000',
            'kontak_disetujui' => true,
        ]);
    }
}
