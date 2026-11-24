<?php

namespace Tests\Feature;

use App\Enums\Modul;
use App\Models\ApbdesPeriode;
use App\Models\Aspirasi;
use App\Models\Berita;
use App\Models\Komoditas;
use App\Models\PerangkatDesa;
use App\Models\ProfilDesa;
use App\Models\User;
use App\Support\KelengkapanSitus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardRingkasanTest extends TestCase
{
    use RefreshDatabase;

    public function test_sapaan_memakai_nama_depan_dan_tanggal(): void
    {
        $this->travelTo(now()->setTime(9, 0));
        $admin = User::factory()->superAdmin()->create(['name' => 'Bayu Setiawan']);

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()
            ->assertSee('Selamat pagi, Bayu')
            ->assertSee(now()->locale('id')->translatedFormat('j F Y'));
    }

    public function test_kartu_kelengkapan_menampilkan_butir_belum_selesai_dan_persentase(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $html = $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()
            ->assertSee('Kelengkapan data situs')
            ->assertSee('0 dari 7 selesai')
            ->assertSee('aria-valuenow="0"', false)
            ->assertSee(route('admin.apbdes'), false)
            ->getContent();

        $this->assertStringContainsString('Kontak resmi desa', $html);
        $this->assertStringContainsString('Berita pertama', $html);
    }

    public function test_data_contoh_tidak_dihitung_dan_butir_selesai_ditandai(): void
    {
        Berita::factory()->create(['contoh' => true]);
        $this->assertFalse($this->butir('berita'));

        Berita::factory()->create();
        Komoditas::factory()->create();
        $this->assertTrue($this->butir('berita'));
        $this->assertTrue($this->butir('potensi'));
    }

    public function test_kartu_kelengkapan_hilang_bila_semua_selesai(): void
    {
        ProfilDesa::factory()->create([
            'telepon' => '0812', 'email' => 'desa@contoh.test', 'jam_layanan' => 'Senin–Jumat 08.00–15.00',
            'kontak_terverifikasi' => true, 'visi' => 'Visi resmi', 'visi_misi_resmi' => true,
            'data_terverifikasi' => true, 'perangkat_resmi' => true,
        ]);
        PerangkatDesa::factory()->create();
        ApbdesPeriode::factory()->create();
        Komoditas::factory()->create();
        Berita::factory()->create();

        $this->actingAs(User::factory()->superAdmin()->create())->get(route('admin.dashboard'))->assertOk()
            ->assertDontSee('Kelengkapan data situs');
    }

    public function test_staf_hanya_melihat_butir_modulnya(): void
    {
        $staf = User::factory()->denganModul([Modul::Berita])->create();

        $this->actingAs($staf)->get(route('admin.dashboard'))->assertOk()
            ->assertSee('0 dari 1 selesai')
            ->assertSee('Berita pertama')
            ->assertDontSee('Kontak resmi desa')
            ->assertDontSee('APBDes diterbitkan');
    }

    public function test_aspirasi_baru_ditonjolkan_dan_angka_nol_menawarkan_aksi(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()
            ->assertSee('Tulis berita')
            ->assertDontSee('border-danger/40', false);

        Aspirasi::factory()->count(2)->create();

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()
            ->assertSee('border-danger/40', false)
            ->assertSee('Tinjau');
    }

    private function butir(string $kunci): bool
    {
        return collect(KelengkapanSitus::daftar())->firstWhere('kunci', $kunci)['selesai'];
    }
}
