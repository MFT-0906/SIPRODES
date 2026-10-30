<?php

namespace Tests\Feature;

use Tests\TestCase;

class Fase7StatusTest extends TestCase
{
    public function test_halaman_404_memakai_tampilan_desa(): void
    {
        $this->get('/halaman-yang-tidak-ada')
            ->assertNotFound()
            ->assertSee('Halaman tidak ditemukan')
            ->assertSee('Kembali ke Beranda')
            ->assertSee('Lihat Berita');
    }

    public function test_berita_draf_menampilkan_404_desa(): void
    {
        $this->get('/berita/draf-belum-terbit')->assertNotFound()->assertSee('Halaman tidak ditemukan');
    }

    public function test_halaman_500_tanpa_detail_teknis(): void
    {
        $html = view('errors.500')->render();

        $this->assertStringContainsString('Terjadi gangguan pada situs', $html);
        $this->assertStringNotContainsString('Stack trace', $html);
    }

    public function test_parameter_demo_diabaikan_di_luar_lokal(): void
    {
        $this->get('/berita?demo=memuat')->assertOk()->assertDontSee('Memuat berita');
        $this->get('/transparansi-apbdes?demo=gagal')->assertOk()->assertDontSee('gagal dimuat');
    }

    public function test_keadaan_contoh_di_lokal(): void
    {
        $this->app->detectEnvironment(fn () => 'local');

        $this->get('/berita?demo=kosong')->assertOk()
            ->assertSee('Belum ada berita yang dipublikasikan')->assertSee('Lihat Profil Desa');
        $this->get('/berita?demo=memuat')->assertOk()->assertSee('Memuat berita');
        $this->get('/transparansi-apbdes?demo=gagal')->assertOk()
            ->assertSee('Data APBDes gagal dimuat')->assertSee('Coba lagi');
    }
}
