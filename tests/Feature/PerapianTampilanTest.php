<?php

namespace Tests\Feature;

use App\Enums\Modul;
use App\Models\KelompokTani;
use App\Models\ProfilDesa;
use App\Models\Umkm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Perapian tampilan Tahap 2 dan 3: visi-misi tanpa penampung, kartu kontak yang tidak kosong berulang,
 * halaman 403 di kerangka admin bagi petugas yang sudah masuk, sumber statistik yang ditulis sekali,
 * dan kolom kontak kelompok tani yang hanya tampil bila ada kontak yang disetujui.
 */
class PerapianTampilanTest extends TestCase
{
    use RefreshDatabase;

    public function test_profil_tanpa_naskah_menampilkan_keterangan_bukan_visi_misi_penampung(): void
    {
        $this->get('/profil-desa')
            ->assertOk()
            ->assertSee('Naskah visi dan misi belum tersedia')
            ->assertSee('Menunggu naskah resmi desa')
            ->assertDontSee('Butir misi 1')
            ->assertDontSee('Visi resmi Desa Marga Agung');
    }

    public function test_profil_dengan_visi_tanpa_misi_menampilkan_visi_dan_keterangan_misi(): void
    {
        ProfilDesa::factory()->create(['visi' => 'Desa mandiri dan sejahtera.', 'misi' => []]);

        $this->get('/profil-desa')
            ->assertOk()
            ->assertSee('Desa mandiri dan sejahtera.')
            ->assertSee('Naskah misi belum tersedia')
            ->assertDontSee('Naskah visi dan misi belum tersedia')
            ->assertDontSee('Butir misi 1');
    }

    public function test_profil_dengan_misi_tanpa_visi_menampilkan_misi_dan_keterangan_visi(): void
    {
        ProfilDesa::factory()->create(['visi' => null, 'misi' => ['Meningkatkan hasil pertanian warga.']]);

        $this->get('/profil-desa')
            ->assertOk()
            ->assertSee('Meningkatkan hasil pertanian warga.')
            ->assertSee('Naskah visi belum tersedia')
            ->assertDontSee('Naskah visi dan misi belum tersedia');
    }

    public function test_kontak_belum_diverifikasi_menggabungkan_telepon_dan_email_dalam_satu_kartu(): void
    {
        $this->get('/kontak-lokasi')
            ->assertOk()
            ->assertSee('Telepon dan email resmi')
            ->assertSee('akan ditampilkan setelah diverifikasi')
            ->assertSee('Formulir daring')
            ->assertDontSee('Telepon kantor desa')
            ->assertDontSee('Email resmi desa');
    }

    public function test_kontak_terverifikasi_menampilkan_kartu_telepon_dan_email_tanpa_keterangan_gabungan(): void
    {
        ProfilDesa::factory()->terverifikasi()->create(['telepon' => '0721 123456', 'email' => 'kantor@margaagung.test']);

        $this->get('/kontak-lokasi')
            ->assertOk()
            ->assertSee('href="tel:0721123456"', false)
            ->assertSee('href="mailto:kantor@margaagung.test"', false)
            ->assertSee('Formulir daring')
            ->assertDontSee('Telepon dan email resmi');
    }

    public function test_kontak_hanya_telepon_terverifikasi_tidak_menampilkan_kartu_email_kosong(): void
    {
        ProfilDesa::factory()->terverifikasi()->create(['telepon' => '0721 123456', 'email' => null]);

        $this->get('/kontak-lokasi')
            ->assertOk()
            ->assertSee('href="tel:0721123456"', false)
            ->assertDontSee('href="mailto:', false)
            ->assertDontSee('Telepon dan email resmi')
            ->assertDontSee('Email resmi desa');
    }

    public function test_403_bagi_petugas_masuk_tampil_dalam_kerangka_admin_dengan_menu_miliknya(): void
    {
        $staf = User::factory()->denganModul([Modul::Berita])->create();

        $this->actingAs($staf)
            ->get(route('admin.apbdes'))
            ->assertForbidden()
            ->assertSee('Anda tidak memiliki izin untuk membuka halaman ini')
            ->assertSee('aria-label="Menu admin"', false)
            ->assertSee(route('admin.berita.index'), false)
            ->assertDontSee(route('admin.akun.index'), false);
    }

    public function test_ringkasan_staf_menyebut_modul_yang_ditugaskan(): void
    {
        $staf = User::factory()->denganModul([Modul::Berita, Modul::Aspirasi])->create();

        $this->actingAs($staf)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Modul Anda: Berita & Agenda, Aspirasi Warga.')
            ->assertDontSee('Belum ada modul yang ditugaskan');
    }

    public function test_ringkasan_staf_tanpa_modul_meminta_menghubungi_super_admin(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Belum ada modul yang ditugaskan kepada Anda. Hubungi Super Admin desa.')
            ->assertDontSee('Modul Anda:');
    }

    public function test_ringkasan_super_admin_tidak_menampilkan_daftar_modul(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Ringkasan konten situs, dihitung dari data terbaru.')
            ->assertDontSee('Modul Anda:')
            ->assertDontSee('Belum ada modul yang ditugaskan');
    }

    public function test_halaman_500_menawarkan_jalur_menghubungi_desa(): void
    {
        $this->view('errors.500')
            ->assertSee('Kembali ke Beranda')
            ->assertSee('Hubungi Desa')
            ->assertSee(route('kontak'), false);
    }

    public function test_403_bagi_pengunjung_publik_tetap_memakai_tata_letak_situs(): void
    {
        $this->view('errors.403')
            ->assertSee('Kembali ke Beranda')
            ->assertDontSee('aria-label="Menu admin"', false);
    }

    public function test_beranda_menulis_sumber_statistik_sekali_bukan_di_setiap_angka(): void
    {
        ProfilDesa::factory()->create(['sumber_data' => 'Survei awal 2026 (contoh uji)']);

        $respons = $this->get('/');

        $respons->assertOk()->assertSee('Sumber: Survei awal 2026 (contoh uji)');
        $this->assertSame(1, substr_count($respons->getContent(), 'Survei awal 2026 (contoh uji)'));
    }

    public function test_profil_menulis_sumber_fakta_dengan_awalan_sumber(): void
    {
        ProfilDesa::factory()->create(['sumber_data' => 'Survei awal 2026 (contoh uji)']);

        $this->get('/profil-desa')
            ->assertOk()
            ->assertSee('Sumber: Survei awal 2026 (contoh uji)');
    }

    public function test_profil_dan_beranda_tanpa_sumber_tidak_menggandakan_kata_sumber(): void
    {
        $this->get('/profil-desa')
            ->assertOk()
            ->assertSee('Sumber belum dicantumkan')
            ->assertDontSee('Sumber: Sumber');

        $this->get('/')
            ->assertOk()
            ->assertSee('Sumber belum dicantumkan')
            ->assertDontSee('Sumber: Sumber');
    }

    public function test_keterangan_peta_tidak_menyebut_roda_mouse(): void
    {
        ProfilDesa::factory()->terverifikasi()->create();

        $this->get('/kontak-lokasi')
            ->assertOk()
            ->assertSee('Gunakan tombol + dan − untuk memperbesar peta.')
            ->assertDontSee('roda mouse');
    }

    public function test_potensi_umkm_tanpa_hasil_pencarian_tidak_menampilkan_hitungan_nol(): void
    {
        Umkm::factory()->create(['nama' => 'Anyaman Bambu Uji']);

        $this->get('/potensi-desa?tab=umkm&q=zzzxyz')
            ->assertOk()
            ->assertSee('Tidak ada usaha yang cocok dengan pencarian.')
            ->assertDontSee('Menampilkan 0–0 dari 0 UMKM');
    }

    public function test_potensi_umkm_dengan_hasil_menampilkan_hitungan(): void
    {
        Umkm::factory()->create(['nama' => 'Anyaman Bambu Uji']);

        $this->get('/potensi-desa?tab=umkm')
            ->assertOk()
            ->assertSee('Anyaman Bambu Uji')
            ->assertSee('Menampilkan 1–1 dari 1 UMKM');
    }

    public function test_potensi_tanpa_kontak_disetujui_menyembunyikan_kolom_kontak_dan_menulis_satu_catatan(): void
    {
        KelompokTani::factory()->count(2)->create();

        $this->get('/potensi-desa')
            ->assertOk()
            ->assertSee('Kontak kelompok tani akan ditampilkan setelah disetujui')
            ->assertDontSee('KONTAK')
            ->assertDontSee('Belum disetujui untuk publikasi');
    }

    public function test_potensi_dengan_kontak_disetujui_menampilkan_kolom_kontak(): void
    {
        KelompokTani::factory()->kontakDisetujui()->create(['nama' => 'Kelompok Tani Bersepakat']);
        KelompokTani::factory()->create(['nama' => 'Kelompok Tani Belum Sepakat']);

        $this->get('/potensi-desa')
            ->assertOk()
            ->assertSee('KONTAK')
            ->assertSee('081200000000')
            ->assertSee('Belum disetujui untuk publikasi')
            ->assertDontSee('Kontak kelompok tani akan ditampilkan setelah disetujui');
    }
}
