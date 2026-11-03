<?php

namespace Database\Factories;

use App\Enums\Modul;
use App\Models\ModulAkses;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ModulAkses>
 */
class ModulAksesFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'modul' => fake()->randomElement(Modul::cases()),
        ];
    }
}
