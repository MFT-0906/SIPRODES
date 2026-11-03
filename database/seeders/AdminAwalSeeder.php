<?php

namespace Database\Seeders;

use App\Enums\Peran;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Membuat satu Super Admin dari ADMIN_EMAIL. Kata sandi acak dicetak sekali ke konsol dan tidak disimpan di tempat lain.
 */
class AdminAwalSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('desa.admin.email');

        if (blank($email)) {
            $this->command?->warn('ADMIN_EMAIL kosong di .env; Super Admin awal tidak dibuat.');

            return;
        }

        if (User::query()->where('email', $email)->exists()) {
            $this->command?->info("Akun {$email} sudah ada; dilewati.");

            return;
        }

        $kataSandi = Str::password(16, symbols: false);

        User::query()->create([
            'name' => config('desa.admin.nama'),
            'email' => $email,
            'password' => $kataSandi,
            'peran' => Peran::SuperAdmin,
            'aktif' => true,
        ]);

        $this->command?->warn("Super Admin dibuat: {$email}");
        $this->command?->warn("Kata sandi (catat sekarang, tidak akan ditampilkan lagi): {$kataSandi}");
    }
}
