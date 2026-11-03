<?php

namespace Database\Factories;

use App\Models\ProfilDesa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProfilDesa>
 */
class ProfilDesaFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sejarah' => fake()->paragraphs(2, true),
            'visi' => fake()->sentence(),
            'misi' => [fake()->sentence(), fake()->sentence()],
            'visi_misi_resmi' => false,
            'alamat_wilayah' => 'Desa Marga Agung, Kecamatan Jati Agung, Kabupaten Lampung Selatan, Provinsi Lampung',
            'penduduk' => fake()->numberBetween(1000, 9000),
            'kk' => fake()->numberBetween(300, 3000),
            'luas_ha' => fake()->numberBetween(500, 2000),
            'jumlah_dusun' => 6,
            'kontak_terverifikasi' => false,
            'data_terverifikasi' => false,
        ];
    }

    public function terverifikasi(): static
    {
        return $this->state(fn (array $attributes) => [
            'alamat' => fake()->streetAddress(),
            'telepon' => '0721000000',
            'email' => fake()->safeEmail(),
            'jam_layanan' => 'Senin–Jumat, 08.00–15.00 WIB',
            'lintang' => -5.3500000,
            'bujur' => 105.2500000,
            'kontak_terverifikasi' => true,
            'data_terverifikasi' => true,
        ]);
    }
}
