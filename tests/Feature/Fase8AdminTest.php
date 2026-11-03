<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MasukSebagaiAdmin;
use Tests\TestCase;

class Fase8AdminTest extends TestCase
{
    use MasukSebagaiAdmin, RefreshDatabase;

    public function test_halaman_masuk_dan_lupa_kata_sandi_tampil(): void
    {
        $this->get(route('admin.login'))->assertOk()
            ->assertSee('Masuk ke Dashboard')->assertSee('Lupa kata sandi?')->assertSee('Ingat saya');
        $this->get(route('admin.password.request'))->assertOk()
            ->assertSee('Kirim tautan atur ulang')->assertDontSee('Jika email terdaftar, tautan telah dikirim');
    }

    public function test_login_kosong_memberi_galat_per_kolom(): void
    {
        $this->from(route('admin.login'))->post(route('admin.login.store'), [])
            ->assertRedirect(route('admin.login'))
            ->assertSessionHasErrors(['login', 'password']);
    }

    public function test_semua_menu_admin_membuka_halaman(): void
    {
        $this->masukSebagaiAdmin();

        foreach (['dashboard', 'profil', 'struktur', 'apbdes', 'pertanian', 'kelompok-tani', 'umkm', 'berita.index', 'agenda.index', 'aspirasi.index'] as $nama) {
            $this->get(route('admin.'.$nama))->assertOk()->assertSee('Dashboard Admin');
        }
    }
}
