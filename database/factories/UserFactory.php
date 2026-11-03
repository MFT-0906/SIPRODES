<?php

namespace Database\Factories;

use App\Enums\Modul;
use App\Enums\Peran;
use App\Models\ModulAkses;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'username' => null,
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'peran' => Peran::Staf,
            'aktif' => true,
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function superAdmin(): static
    {
        return $this->state(fn (array $attributes) => ['peran' => Peran::SuperAdmin]);
    }

    public function nonaktif(): static
    {
        return $this->state(fn (array $attributes) => ['aktif' => false]);
    }

    /**
     * Staf dengan penugasan modul tertentu.
     *
     * @param  list<Modul>  $modul
     */
    public function denganModul(array $modul): static
    {
        return $this->afterCreating(function (User $user) use ($modul) {
            foreach ($modul as $m) {
                ModulAkses::factory()->create(['user_id' => $user->id, 'modul' => $m]);
            }
        });
    }
}
