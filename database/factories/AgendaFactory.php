<?php

namespace Database\Factories;

use App\Enums\StatusTerbit;
use App\Models\Agenda;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Agenda>
 */
class AgendaFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'judul' => fake()->sentence(3),
            'tanggal' => fake()->dateTimeBetween('+1 day', '+60 days'),
            'waktu' => '09:00',
            'lokasi' => null,
            'deskripsi' => null,
            'status' => StatusTerbit::Terbit,
            'contoh' => false,
        ];
    }

    public function draf(): static
    {
        return $this->state(fn (array $attributes) => ['status' => StatusTerbit::Draf]);
    }

    public function lewat(): static
    {
        return $this->state(fn (array $attributes) => [
            'tanggal' => fake()->dateTimeBetween('-60 days', '-1 day'),
        ]);
    }
}
