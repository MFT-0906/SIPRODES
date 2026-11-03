<?php

namespace Tests\Feature;

use App\Enums\StatusAspirasi;
use App\Models\Aspirasi;
use App\Models\Berita;
use App\Models\LogAktivitas;
use App\Models\Umkm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Halaman galat bertema desa, filter/paginasi log, pembersih log, pembersih berkas yatim, dan opsi anonimisasi pesan.
 */
class PemeliharaanTest extends TestCase
{
    use RefreshDatabase;

    // ---- Halaman galat ----

    public function test_halaman_galat_419_429_503_bertema_desa_tanpa_jejak_teknis(): void
    {
        $kasus = ['419' => 'Sesi halaman sudah berakhir', '429' => 'Terlalu banyak permintaan', '503' => 'Situs sedang dipelihara'];

        foreach ($kasus as $kode => $judul) {
            $html = view('errors.'.$kode)->render();

            $this->assertStringContainsString($judul, $html);
            $this->assertStringContainsString('Desa Marga Agung', $html);
            $this->assertStringNotContainsString('Stack trace', $html);
            $this->assertStringNotContainsString('javascript:', $html, "Tautan javascript: diblokir CSP di halaman {$kode}");
        }
    }

    public function test_419_dan_429_tampil_bertema_lewat_permintaan_nyata(): void
    {
        $this->app->detectEnvironment(fn () => 'local');
        $this->post(route('aspirasi.store'), ['nama' => 'X'])->assertStatus(419)->assertSee('Sesi halaman sudah berakhir');

        Route::get('/uji-429', fn () => abort(429))->middleware('web');
        $this->get('/uji-429')->assertStatus(429)->assertSee('Terlalu banyak permintaan');
    }

    // ---- Log aktivitas: filter ----

    public function test_log_dapat_difilter_menurut_aksi_dan_kata_kunci(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());
        LogAktivitas::factory()->create(['aksi' => 'berita.dibuat', 'keterangan' => 'Berita Alfa']);
        LogAktivitas::factory()->create(['aksi' => 'umkm.dibuat', 'keterangan' => 'Usaha Beta']);
        LogAktivitas::factory()->create(['aksi' => 'berita.dihapus', 'keterangan' => 'Berita Gamma']);

        $this->get(route('admin.akun.index', ['aksi' => 'berita.dibuat']))->assertSee('Berita Alfa')->assertDontSee('Usaha Beta')->assertDontSee('Berita Gamma');
        $this->get(route('admin.akun.index', ['qlog' => 'Beta']))->assertSee('Usaha Beta')->assertDontSee('Berita Alfa');
        $this->get(route('admin.akun.index', ['aksi' => 'berita.dibuat', 'qlog' => 'Beta']))->assertSee('Tidak ada aktivitas yang cocok dengan filter.');
        $this->get(route('admin.akun.index', ['aksi' => 'aksi-palsu', 'qlog' => "%' OR 1=1 --"]))->assertOk();
    }

    // ---- log:bersihkan ----

    public function test_perintah_bersihkan_log_menghapus_yang_melewati_masa_simpan(): void
    {
        config(['desa.log.masa_simpan_bulan' => 12]);
        $lama = LogAktivitas::factory()->create(['created_at' => now()->subMonths(13)]);
        $baru = LogAktivitas::factory()->create(['created_at' => now()->subMonths(11)]);

        $this->artisan('log:bersihkan', ['--hitung' => true])->expectsOutputToContain('1 entri log melewati masa simpan')->assertSuccessful();
        $this->assertNotNull($lama->fresh());

        $this->artisan('log:bersihkan')->expectsOutputToContain('1 entri log dihapus.')->assertSuccessful();
        $this->assertNull($lama->fresh());
        $this->assertNotNull($baru->fresh());
    }

    public function test_bersihkan_log_tidak_menghapus_apa_pun_bila_masa_simpan_nol(): void
    {
        config(['desa.log.masa_simpan_bulan' => 0]);
        LogAktivitas::factory()->create(['created_at' => now()->subYears(5)]);

        $this->artisan('log:bersihkan')->expectsOutputToContain('Masa simpan log tidak diatur')->assertSuccessful();
        $this->assertSame(1, LogAktivitas::count());
    }

    // ---- unggahan:bersihkan ----

    public function test_berkas_yatim_dilaporkan_dan_hanya_yang_tua_dan_tak_dirujuk_dihapus(): void
    {
        Storage::fake('public');
        Storage::fake('local');

        Storage::disk('public')->put('berita/dipakai.jpg', 'a');
        Berita::factory()->create(['gambar_path' => 'berita/dipakai.jpg']);
        Storage::disk('public')->put('umkm/dipakai.jpg', 'b');
        Umkm::factory()->create(['foto_path' => 'umkm/dipakai.jpg']);

        Storage::disk('public')->put('berita/yatim-tua.jpg', 'c');
        Storage::disk('public')->put('komoditas/yatim-tua.png', 'd');
        Storage::disk('local')->put('apbdes/yatim-tua.pdf', 'e');
        Storage::disk('public')->put('berita/yatim-baru.jpg', 'f');

        foreach ([['public', 'berita/yatim-tua.jpg'], ['public', 'komoditas/yatim-tua.png'], ['local', 'apbdes/yatim-tua.pdf'], ['public', 'berita/dipakai.jpg'], ['public', 'umkm/dipakai.jpg']] as [$disk, $berkas]) {
            touch(Storage::disk($disk)->path($berkas), now()->subDays(3)->timestamp);
        }

        $this->artisan('unggahan:bersihkan')->expectsOutputToContain('3 berkas yatim ditemukan')->assertSuccessful();
        Storage::disk('public')->assertExists('berita/yatim-tua.jpg');

        $this->artisan('unggahan:bersihkan', ['--hapus' => true])->expectsOutputToContain('3 dari 3 berkas yatim dihapus.')->assertSuccessful();

        Storage::disk('public')->assertMissing('berita/yatim-tua.jpg');
        Storage::disk('public')->assertMissing('komoditas/yatim-tua.png');
        Storage::disk('local')->assertMissing('apbdes/yatim-tua.pdf');
        Storage::disk('public')->assertExists('berita/dipakai.jpg');
        Storage::disk('public')->assertExists('umkm/dipakai.jpg');
        Storage::disk('public')->assertExists('berita/yatim-baru.jpg');
    }

    // ---- Anonimisasi pesan (opsional) ----

    public function test_pesan_ikut_dihapus_saat_anonimisasi_bila_diaktifkan(): void
    {
        config(['desa.aspirasi.masa_simpan_bulan' => 24, 'desa.aspirasi.hapus_pesan_saat_anonim' => true]);
        $a = Aspirasi::factory()->create(['nama' => 'Warga', 'pesan' => 'Isi pribadi sekali.', 'status' => StatusAspirasi::Selesai, 'selesai_at' => now()->subMonths(30)]);

        $this->artisan('aspirasi:anonimkan')->assertSuccessful();

        $a->refresh();
        $this->assertNull($a->nama);
        $this->assertSame('[Isi pesan dihapus saat anonimisasi]', $a->pesan);
    }

    public function test_pesan_dipertahankan_saat_anonimisasi_secara_bawaan(): void
    {
        $this->assertFalse(config('desa.aspirasi.hapus_pesan_saat_anonim'));
        config(['desa.aspirasi.masa_simpan_bulan' => 24]);
        $a = Aspirasi::factory()->create(['pesan' => 'Isi tetap ada.', 'status' => StatusAspirasi::Selesai, 'selesai_at' => now()->subMonths(30)]);

        $this->artisan('aspirasi:anonimkan')->assertSuccessful();

        $this->assertSame('Isi tetap ada.', $a->fresh()->pesan);
    }

    public function test_perintah_pemeliharaan_terdaftar_di_penjadwal(): void
    {
        $this->artisan('schedule:list')->expectsOutputToContain('log:bersihkan')->expectsOutputToContain('unggahan:bersihkan --hapus')->assertSuccessful();
    }
}
