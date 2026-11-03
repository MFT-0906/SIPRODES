<?php

namespace Tests\Feature;

use App\Models\Agenda;
use App\Models\ApbdesPeriode;
use App\Models\Berita;
use Database\Seeders\DataContohSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class Fase7StatusTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DataContohSeeder::class);
    }

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

    public function test_parameter_demo_tidak_lagi_berpengaruh_di_lingkungan_mana_pun(): void
    {
        foreach (['production', 'local'] as $lingkungan) {
            $this->app->detectEnvironment(fn () => $lingkungan);

            $this->get('/berita?demo=kosong')->assertOk()->assertDontSee('Belum ada berita yang dipublikasikan');
            $this->get('/berita?demo=memuat')->assertOk()->assertDontSee('Memuat berita');
            $this->get('/transparansi-apbdes?demo=gagal')->assertOk()->assertDontSee('gagal dimuat');
        }
    }

    public function test_keadaan_kosong_berita_dan_agenda(): void
    {
        Berita::query()->delete();
        Agenda::query()->delete();

        $this->get('/berita')->assertOk()
            ->assertSee('Belum ada berita yang dipublikasikan')->assertSee('Lihat Profil Desa')
            ->assertSee('Belum ada agenda mendatang');
        $this->get('/')->assertOk()->assertSee('Belum ada berita yang dipublikasikan');
    }

    public function test_keadaan_kosong_apbdes_bila_belum_ada_periode_terbit(): void
    {
        ApbdesPeriode::query()->delete();

        $this->get('/transparansi-apbdes')->assertOk()->assertSee('Data APBDes belum dipublikasikan');
    }

    public function test_apbdes_gagal_dimuat_saat_database_bermasalah(): void
    {
        Schema::drop('apbdes_rincian');
        Schema::drop('apbdes_dokumen');
        Schema::drop('apbdes_periode');

        $this->get('/transparansi-apbdes')->assertOk()
            ->assertSee('Data APBDes gagal dimuat')->assertSee('Coba lagi')
            ->assertDontSee('SQLSTATE');
    }
}
