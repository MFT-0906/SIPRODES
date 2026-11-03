<?php

namespace App\Models;

use App\Enums\Modul;
use App\Enums\Peran;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'username', 'password', 'peran', 'aktif'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'peran' => Peran::class,
            'aktif' => 'boolean',
            'terakhir_masuk_at' => 'datetime',
        ];
    }

    /**
     * @return HasMany<ModulAkses, $this>
     */
    public function modulAkses(): HasMany
    {
        return $this->hasMany(ModulAkses::class);
    }

    public function adalahSuperAdmin(): bool
    {
        return $this->peran === Peran::SuperAdmin;
    }

    /**
     * Super Admin memegang semua modul; Staf hanya modul yang ditugaskan.
     */
    public function punyaModul(Modul $modul): bool
    {
        if ($this->adalahSuperAdmin()) {
            return true;
        }

        return $this->modulAkses->contains(fn (ModulAkses $akses) => $akses->modul === $modul);
    }
}
