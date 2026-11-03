<?php

namespace Tests\Feature;

use Database\Seeders\DataContohSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Fase6PagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DataContohSeeder::class);
    }

    public function test_daftar_berita_hanya_menampilkan_yang_terbit(): void
    {
        $this->get('/berita')
            ->assertOk()
            ->assertSee('Gotong royong, lingkungan bersih dan warga makin akrab')
            ->assertDontSee('Draf berita yang belum diterbitkan');
    }

    public function test_draf_dan_slug_tidak_dikenal_tidak_dapat_dibuka(): void
    {
        $this->get('/berita/draf-belum-terbit')->assertNotFound();
        $this->get('/berita/tidak-ada')->assertNotFound();
    }

    public function test_detail_berita_terbit_dapat_dibuka(): void
    {
        $this->get('/berita/gotong-royong-lingkungan-bersih')
            ->assertOk()
            ->assertSee('Berbagi tugas, meringankan pekerjaan')
            ->assertSee('Berita lainnya');
    }

    public function test_filter_kategori_dan_pencarian_berita(): void
    {
        $this->get('/berita?kategori=Pertanian')
            ->assertOk()
            ->assertSee('Mengolah jagung menjadi peluang usaha bersama')
            ->assertDontSee('Gotong royong, lingkungan bersih');

        $this->get('/berita?q=tidakadakatakunciini')
            ->assertOk()
            ->assertSee('Tidak ada berita yang cocok');
    }

    public function test_formulir_aspirasi_menolak_isian_tidak_valid(): void
    {
        $this->post('/ruang-aspirasi', ['nama' => '', 'nik' => '123', 'pesan' => 'Jalan rusak'])
            ->assertSessionHasErrors(['nama', 'nik', 'pesan']);
    }

    public function test_nik_opsional_dan_pesan_valid_diterima(): void
    {
        $this->post('/ruang-aspirasi', [
            'nama' => 'Warga',
            'nik' => '',
            'pesan' => 'Mohon perbaikan jalan di dusun saya, terima kasih.',
        ])
            ->assertRedirect('/ruang-aspirasi')
            ->assertSessionHas('aspirasi_terkirim');

        $this->withSession(['aspirasi_terkirim' => true])
            ->get('/ruang-aspirasi')
            ->assertOk()
            ->assertSee('Aspirasi berhasil dikirim');
    }

    public function test_halaman_aspirasi_memuat_formulir_dan_catatan_privasi(): void
    {
        $this->get('/ruang-aspirasi')
            ->assertOk()
            ->assertSee('Formulir aspirasi')
            ->assertSee('Opsional')
            ->assertSee('Aspirasi tidak ditampilkan ke publik.');
    }

    public function test_kontak_belum_diverifikasi_tidak_menampilkan_tautan_telepon_atau_email(): void
    {
        $this->get('/kontak-lokasi')
            ->assertOk()
            ->assertDontSee('href="tel:', false)
            ->assertDontSee('href="mailto:', false)
            ->assertSee('akan ditampilkan setelah diverifikasi');
    }
}
