<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TampilanMasukTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_masuk_memakai_panel_identitas_dan_teks_terbaru(): void
    {
        $this->get(route('admin.login'))->assertOk()
            ->assertSee('Dashboard Admin')
            ->assertSee('Kelola profil desa, APBDes, potensi, berita, agenda, dan aspirasi warga dari satu tempat.')
            ->assertSee('contoh: nama@gmail.com', false)
            ->assertSee('Koneksi aman · tautan atur ulang dikirim ke email Anda')
            ->assertDontSee('nama@margaagung.desa.id')
            ->assertDontSee('lupa kata sandi dibantu Super Admin')
            // Peringatan penguncian hanya muncul setelah gagal masuk.
            ->assertDontSee('akun dikunci sementara');
    }

    public function test_tombol_tampilkan_kata_sandi_ada_dan_input_tetap_password(): void
    {
        $html = $this->get(route('admin.login'))->assertOk()->getContent();

        $this->assertStringContainsString('aria-label="Tampilkan kata sandi"', $html);
        $this->assertStringContainsString('aria-controls="f-password"', $html);
        $this->assertMatchesRegularExpression('/<input[^>]*id="f-password"[^>]*type="password"/s', $html);
    }

    public function test_peringatan_penguncian_muncul_setelah_gagal_masuk(): void
    {
        User::factory()->create(['email' => 'staf@contoh.test']);

        $this->from(route('admin.login'))->post(route('admin.login.store'), ['login' => 'staf@contoh.test', 'password' => 'salah-sekali'])
            ->assertRedirect(route('admin.login'));

        $this->get(route('admin.login'))->assertSee('akun dikunci sementara');
    }

    public function test_halaman_lupa_dan_atur_ulang_memakai_tata_letak_yang_sama(): void
    {
        $this->get(route('admin.password.request'))->assertOk()
            ->assertSee('Dashboard Admin')
            ->assertSee('contoh: nama@gmail.com', false);

        $html = $this->get(route('admin.password.reset', ['token' => 'abc', 'email' => 'a@contoh.test']))->assertOk()
            ->assertSee('Dashboard Admin')->getContent();
        $this->assertSame(2, substr_count($html, 'aria-label="Tampilkan kata sandi"'));
    }
}
