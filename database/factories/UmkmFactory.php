<?php

namespace Database\Factories;

use App\Enums\StatusTerbit;
use App\Models\Umkm;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Umkm>
 */
class UmkmFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => fake()->unique()->company(),
            'produk' => fake()->words(3, true),
            'deskripsi' => fake()->sentence(10),
            'whatsapp' => null,
            'whatsapp_disetujui' => false,
            'status' => StatusTerbit::Terbit,
            'contoh' => false,
        ];
    }

    public function whatsappDisetujui(): static
    {
        return $this->state(fn (array $attributes) => [
            'whatsapp' => '6281200000000',
            'whatsapp_disetujui' => true,
        ]);
    }

    public function draf(): static
    {
        return $this->state(fn (array $attributes) => ['status' => StatusTerbit::Draf]);
    }
}
