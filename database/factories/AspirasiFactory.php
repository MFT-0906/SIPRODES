<?php

namespace Database\Factories;

use App\Enums\StatusAspirasi;
use App\Models\Aspirasi;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Aspirasi>
 */
class AspirasiFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => fake()->name(),
            'nik' => null,
            'pesan' => fake()->paragraph(),
            'status' => StatusAspirasi::Baru,
            'contoh' => false,
        ];
    }

    public function denganNik(string $nik = '1871010101010001'): static
    {
        return $this->state(fn (array $attributes) => ['nik' => $nik]);
    }

    public function dibaca(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => StatusAspirasi::Dibaca,
            'dibaca_at' => now(),
        ]);
    }

    public function selesai(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => StatusAspirasi::Selesai,
            'dibaca_at' => now()->subDay(),
            'selesai_at' => now(),
        ]);
    }
}
