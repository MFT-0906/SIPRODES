<?php

namespace Tests\Feature;

use Database\Seeders\DataContohSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DataContohSeeder::class);
    }

    public function test_semua_halaman_publik_dapat_dibuka(): void
    {
        foreach (['/', '/profil-desa', '/transparansi-apbdes', '/potensi-desa', '/berita', '/berita/gotong-royong-lingkungan-bersih', '/ruang-aspirasi', '/kontak-lokasi'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_apbdes_menampilkan_angka_kosong_bukan_angka_karangan(): void
    {
        $this->get('/transparansi-apbdes')
            ->assertOk()
            ->assertSee('Rp —', false)
            ->assertSee('Diagram belum tersedia')
            ->assertSee('Data contoh');
    }

    public function test_apbdes_tahun_tanpa_dokumen_menampilkan_keadaan_kosong(): void
    {
        $this->get('/transparansi-apbdes?tahun=2024')
            ->assertOk()
            ->assertSee('Dokumen APBDes tahun 2024 belum tersedia');
    }

    public function test_apbdes_tahun_tidak_dikenal_kembali_ke_tahun_bawaan(): void
    {
        $this->get('/transparansi-apbdes?tahun=1999')
            ->assertOk()
            ->assertSee('Peraturan Desa tentang APBDes Tahun 2026');
    }

    public function test_potensi_tidak_menampilkan_whatsapp_yang_belum_disetujui(): void
    {
        $this->get('/potensi-desa')
            ->assertOk()
            ->assertSee('Dapur Jagung Bersama')
            ->assertDontSee('Hubungi via WhatsApp')
            ->assertDontSee('wa.me');
    }

    public function test_kontak_resmi_footer_disembunyikan_sebelum_diverifikasi(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertDontSee('Kontak Resmi');
    }
}
