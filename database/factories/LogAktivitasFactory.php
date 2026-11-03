<?php

namespace Database\Factories;

use App\Models\LogAktivitas;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LogAktivitas>
 */
class LogAktivitasFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => null,
            'aksi' => 'masuk',
            'keterangan' => fake()->sentence(),
            'ip' => fake()->ipv4(),
        ];
    }
}
