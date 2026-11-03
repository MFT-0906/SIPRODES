<?php

namespace Database\Factories;

use App\Enums\StatusTerbit;
use App\Models\Berita;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Berita>
 */
class BeritaFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $judul = rtrim(fake()->unique()->sentence(6), '.');

        return [
            'judul' => $judul,
            'slug' => Str::slug($judul).'-'.fake()->unique()->numberBetween(1, 99999),
            'kategori' => fake()->randomElement(['Kegiatan', 'Pertanian', 'Pemerintahan', 'Pengumuman']),
            'ringkasan' => fake()->sentence(12),
            'isi' => fake()->paragraph()."\n\n".fake()->paragraph(),
            'tanggal' => fake()->dateTimeBetween('-60 days', 'now'),
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
