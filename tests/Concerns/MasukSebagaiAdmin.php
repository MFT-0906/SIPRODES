<?php

namespace Tests\Concerns;

use App\Models\User;

trait MasukSebagaiAdmin
{
    protected function masukSebagaiAdmin(): User
    {
        $user = User::factory()->superAdmin()->create(['name' => 'Nama Admin']);
        $this->actingAs($user);

        return $user;
    }
}
