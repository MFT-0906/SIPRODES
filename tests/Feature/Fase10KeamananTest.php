<?php

namespace Tests\Feature;

use App\Enums\Modul;
use App\Enums\StatusAspirasi;
use App\Models\Agenda;
use App\Models\ApbdesDokumen;
use App\Models\ApbdesPeriode;
use App\Models\Aspirasi;
use App\Models\Berita;
use App\Models\Dusun;
use App\Models\Jabatan;
use App\Models\LogAktivitas;
use App\Models\PerangkatDesa;
use App\Models\ProfilDesa;
use App\Models\User;
use App\Support\Produksi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Str;
use Tests\Concerns\MasukSebagaiAdmin;
use Tests\TestCase;

class Fase10KeamananTest extends TestCase
{
    use MasukSebagaiAdmin, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Storage::fake('local');
        // Jangan bergantung pada server dev Vite yang mungkin sedang berjalan (public/hot).
        Vite::useHotFile(storage_path('framework/testing/vite-hot-tidak-ada'));
    }

    private function aksi(): array
    {
        return LogAktivitas::query()->orderBy('id')->pluck('aksi')->all();
    }

    // ---- Header keamanan ----

    public function test_header_keamanan_ada_di_halaman_publik_admin_dan_galat(): void
    {
        $this->masukSebagaiAdmin();

        foreach (['/', '/berita', '/admin', '/tidak-ada-halaman'] as $url) {
            $respons = $this->get($url);
            $this->assertSame('DENY', $respons->headers->get('X-Frame-Options'), $url);
            $this->assertSame('nosniff', $respons->headers->get('X-Content-Type-Options'), $url);
            $this->assertSame('strict-origin-when-cross-origin', $respons->headers->get('Referrer-Policy'), $url);
            $this->assertStringContainsString('camera=()', $respons->headers->get('Permissions-Policy'), $url);
            $this->assertNotNull($respons->headers->get('Content-Security-Policy'), $url);
        }
    }

    public function test_csp_membatasi_sumber_dan_melarang_skrip_inline(): void
    {
        $csp = $this->get('/')->headers->get('Content-Security-Policy');

        $this->assertStringContainsString("default-src 'self'", $csp);
        $this->assertStringContainsString("object-src 'none'", $csp);
        $this->assertStringContainsString("img-src 'self' data: https://server.arcgisonline.com;", $csp);
        $this->assertStringNotContainsString('frame-src', $csp);
        $this->assertStringContainsString("frame-ancestors 'none'", $csp);
        $this->assertStringContainsString("form-action 'self'", $csp);
        $this->assertStringContainsString("base-uri 'self'", $csp);
        $this->assertMatchesRegularExpression("/script-src 'self' 'unsafe-eval'(;|$)/", $csp);
        $this->assertDoesNotMatchRegularExpression("/script-src[^;]*'unsafe-inline'/", $csp);
        $this->assertStringNotContainsString('*', $csp);
    }

    public function test_halaman_tidak_memuat_skrip_inline_yang_diblokir_csp(): void
    {
        $this->masukSebagaiAdmin();

        foreach (['/', '/berita', '/profil-desa', '/ruang-aspirasi', '/admin', '/admin/berita/tambah', '/admin/apbdes'] as $url) {
            $html = $this->get($url)->getContent();
            preg_match_all('/<script\b([^>]*)>/i', $html, $cocok);
            foreach ($cocok[1] as $atribut) {
                $this->assertStringContainsString('src=', $atribut, "Skrip inline di {$url}");
            }
            $this->assertDoesNotMatchRegularExpression('/\son(click|load|error|submit)\s*=/i', $html, "Atribut event inline di {$url}");
        }
    }

    public function test_hsts_hanya_dikirim_lewat_https(): void
    {
        $this->assertNull($this->get('http://localhost/')->headers->get('Strict-Transport-Security'));

        $this->assertStringContainsString('max-age=31536000', $this->get('https://localhost/')->headers->get('Strict-Transport-Security'));
    }

    public function test_csp_tidak_dikirim_saat_server_dev_vite_aktif(): void
    {
        $hot = storage_path('framework/testing/vite-hot-uji');
        File::ensureDirectoryExists(dirname($hot));
        File::put($hot, 'http://[::1]:5173');
        Vite::useHotFile($hot);

        try {
            $respons = $this->get('/');
            $this->assertNull($respons->headers->get('Content-Security-Policy'));
            $this->assertSame('DENY', $respons->headers->get('X-Frame-Options'));
        } finally {
            File::delete($hot);
        }
    }

    // ---- Pengaman produksi ----

    public function test_produksi_mematikan_debug_memaksa_https_dan_mengamankan_cookie(): void
    {
        config(['app.debug' => true, 'session.secure' => null]);
        $this->app->detectEnvironment(fn () => 'production');

        Produksi::terapkan($this->app);

        $this->assertFalse(config('app.debug'));
        $this->assertTrue(config('session.secure'));
        $this->assertTrue(config('session.http_only'));
        $this->assertSame('lax', config('session.same_site'));
        $this->assertStringStartsWith('https://', url('/'));
        URL::forceScheme('http');
    }

    public function test_lingkungan_lokal_tidak_diubah_pengaman_produksi(): void
    {
        config(['app.debug' => true]);

        Produksi::terapkan($this->app);

        $this->assertTrue(config('app.debug'));
        $this->assertTrue(config('session.http_only'));
        $this->assertSame('lax', config('session.same_site'));
    }

    public function test_zona_waktu_aplikasi_wib(): void
    {
        $this->assertSame('Asia/Jakarta', config('app.timezone'));
    }

    // ---- Audit log: autentikasi ----

    public function test_masuk_keluar_dan_penguncian_dicatat(): void
    {
        $user = User::factory()->create(['email' => 'a@desa.test']);

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('admin.login.store'), ['login' => 'a@desa.test', 'password' => 'salah']);
        }
        $this->post(route('admin.login.store'), ['login' => 'a@desa.test', 'password' => 'salah']);

        $kunci = LogAktivitas::where('aksi', 'masuk.dikunci')->sole();
        $this->assertNull($kunci->user_id);
        $this->assertStringContainsString('a@desa.test', $kunci->keterangan);
        $this->assertStringNotContainsString('salah', $kunci->keterangan);

        RateLimiter::clear(Str::transliterate('a@desa.test|127.0.0.1'));
        $this->post(route('admin.login.store'), ['login' => 'a@desa.test', 'password' => 'password'])->assertRedirect();
        $this->post(route('admin.logout'));

        $this->assertSame(['masuk.dikunci', 'masuk', 'keluar'], $this->aksi());
        $this->assertSame($user->id, LogAktivitas::where('aksi', 'masuk')->value('user_id'));
        $this->assertNotNull(LogAktivitas::where('aksi', 'masuk')->value('ip'));
    }

    // ---- Audit log: konten ----

    public function test_publikasi_ubah_dan_hapus_konten_dicatat(): void
    {
        $admin = $this->masukSebagaiAdmin();
        $data = ['title' => 'Berita Audit', 'excerpt' => 'R', 'isi_teks' => 'Isi', 'tanggal' => '2026-10-29', 'kategori' => 'Kegiatan', 'status' => 'draf'];

        $this->post(route('admin.berita.store'), $data + ['aksi' => 'draf']);
        $this->put(route('admin.berita.update', 'berita-audit'), $data + ['aksi' => 'terbit']);
        $this->put(route('admin.berita.update', 'berita-audit'), $data + ['aksi' => 'terbit']);
        $this->put(route('admin.berita.update', 'berita-audit'), $data + ['aksi' => 'draf']);
        $this->delete(route('admin.berita.destroy', 'berita-audit'));

        $this->assertSame([
            'berita.dibuat',
            'berita.diubah', 'berita.diterbitkan',
            'berita.diubah',
            'berita.diubah', 'berita.ditarik',
            'berita.dihapus',
        ], $this->aksi());
        $this->assertSame($admin->id, LogAktivitas::first()->user_id);
        $this->assertSame('Berita Audit', LogAktivitas::where('aksi', 'berita.dihapus')->value('keterangan'));
        $this->assertSame(Berita::class, LogAktivitas::first()->subjek_type);
    }

    public function test_agenda_profil_struktur_dan_potensi_dicatat(): void
    {
        $this->masukSebagaiAdmin();
        Jabatan::factory()->create(['nama' => 'Kaur']);

        $this->post(route('admin.agenda.store'), ['title' => 'Rapat', 'tanggal' => now()->addDay()->toDateString(), 'status' => 'terbit', 'aksi' => 'terbit']);
        $this->delete(route('admin.agenda.destroy', Agenda::sole()->id));
        $this->put(route('admin.profil.simpan', 'sejarah'), ['sejarah' => 'Sejarah']);
        $this->put(route('admin.profil.simpan', 'lokasi'), ['alamat' => 'A', 'lintang' => 1, 'bujur' => 1, 'terverifikasi' => '1', 'aksi' => 'terbit']);
        $this->put(route('admin.profil.simpan', 'lokasi'), ['alamat' => 'A', 'lintang' => 1, 'bujur' => 1, 'aksi' => 'terbit']);
        $this->post(route('admin.struktur.store'), ['nama' => 'Bu Kaur', 'jabatan' => 'Kaur']);
        $this->post(route('admin.struktur.geser', PerangkatDesa::sole()->id), ['arah' => 'naik']);
        $this->delete(route('admin.struktur.destroy', PerangkatDesa::sole()->id));
        $this->post(route('admin.pertanian.store'), ['nama' => 'Jagung', 'status' => 'terbit']);
        $this->post(route('admin.umkm.store'), ['nama' => 'Usaha', 'produk' => 'P', 'status' => 'draf']);

        $aksi = $this->aksi();
        foreach (['agenda.dibuat', 'agenda.diterbitkan', 'agenda.dihapus', 'profil.diubah', 'profil.kontak_diterbitkan', 'profil.kontak_ditarik',
            'perangkat.dibuat', 'perangkat.urutan', 'perangkat.dihapus', 'komoditas.dibuat', 'komoditas.diterbitkan', 'umkm.dibuat'] as $diharapkan) {
            $this->assertContains($diharapkan, $aksi, "Log {$diharapkan} tidak ada.");
        }
        $this->assertNotContains('umkm.diterbitkan', $aksi);
    }

    public function test_apbdes_dan_dokumen_pdf_dicatat(): void
    {
        $this->masukSebagaiAdmin();
        $pdf = UploadedFile::fake()->createWithContent('a.pdf', "%PDF-1.4\n%%EOF");

        $this->post(route('admin.apbdes.store'), ['tahun' => 2030, 'periode' => 'Jan–Des', 'status' => 'terbit', 'aksi' => 'terbit', 'dokumen' => $pdf, 'judul_dokumen' => 'Perdes 2030']);
        $this->delete(route('admin.apbdes.dokumen.destroy', ApbdesDokumen::sole()->id));
        $this->delete(route('admin.apbdes.destroy', ApbdesPeriode::sole()->id));

        $this->assertSame(['apbdes.dibuat', 'apbdes.diterbitkan', 'apbdes.dokumen_diunggah', 'apbdes.dokumen_dihapus', 'apbdes.dihapus'], $this->aksi());
        $this->assertSame('Perdes 2030', LogAktivitas::where('aksi', 'apbdes.dokumen_diunggah')->value('keterangan'));
    }

    public function test_status_aspirasi_dicatat_tanpa_data_pribadi(): void
    {
        $this->masukSebagaiAdmin();
        $a = Aspirasi::factory()->denganNik('1871010101010001')->create(['nama' => 'Warga Rahasia', 'pesan' => 'Isi pesan pribadi sekali ya.']);

        $this->put(route('admin.aspirasi.status', $a->id), ['status' => 'baru', 'aksi' => 'selesai', 'catatan' => 'Catatan internal rahasia']);
        $this->put(route('admin.aspirasi.status', $a->id), ['status' => 'selesai', 'catatan' => 'Catatan lain']);

        $this->assertSame(['aspirasi.status'], $this->aksi());
        $log = LogAktivitas::sole();
        $this->assertSame('Baru → Selesai', $log->keterangan);
        $semua = json_encode(LogAktivitas::all()->toArray());
        foreach (['Warga Rahasia', 'Isi pesan pribadi', '1871010101010001', 'Catatan internal'] as $rahasia) {
            $this->assertStringNotContainsString($rahasia, $semua);
        }
    }

    public function test_perubahan_akun_dan_peran_dicatat_dengan_selisih(): void
    {
        $this->masukSebagaiAdmin();
        $this->post(route('admin.akun.store'), ['nama' => 'Staf', 'email' => 'staf@desa.test', 'peran' => 'staf', 'modul' => ['berita']]);
        $staf = User::where('email', 'staf@desa.test')->sole();

        $this->put(route('admin.akun.update', $staf), ['nama' => 'Staf', 'email' => 'staf@desa.test', 'peran' => 'staf', 'modul' => ['apbdes'], 'aktif' => '1']);
        $this->put(route('admin.akun.update', $staf), ['nama' => 'Staf', 'email' => 'staf@desa.test', 'peran' => 'super_admin', 'aktif' => '1']);
        $this->put(route('admin.akun.update', $staf), ['nama' => 'Staf', 'email' => 'staf@desa.test', 'peran' => 'super_admin']);
        $this->post(route('admin.akun.reset', User::factory()->create()));

        $this->assertSame(['akun.dibuat', 'akun.diubah', 'akun.diubah', 'akun.diubah', 'akun.tautan_dikirim'], $this->aksi());
        $diubah = LogAktivitas::where('aksi', 'akun.diubah')->orderBy('id')->pluck('keterangan');
        $this->assertStringContainsString('modul [berita] → [apbdes]', $diubah[0]);
        $this->assertStringContainsString('peran Staf → Super Admin', $diubah[1]);
        $this->assertStringContainsString('dinonaktifkan', $diubah[2]);
        $this->assertStringNotContainsString('password', strtolower(json_encode(LogAktivitas::all()->toArray())));
    }

    public function test_pengaturan_ulang_kata_sandi_dicatat(): void
    {
        $user = User::factory()->create(['email' => 'lupa@desa.test']);
        $token = Password::createToken($user);

        $this->post(route('admin.password.update'), ['token' => $token, 'email' => $user->email, 'password' => 'KataSandiBaru123', 'password_confirmation' => 'KataSandiBaru123']);

        $this->assertSame(['kata_sandi.diatur_ulang'], $this->aksi());
        $this->assertStringNotContainsString('KataSandiBaru123', json_encode(LogAktivitas::all()->toArray()));
    }

    public function test_kegagalan_menulis_log_tidak_menggagalkan_tindakan(): void
    {
        $this->masukSebagaiAdmin();
        Schema::drop('log_aktivitas');

        $this->post(route('admin.berita.store'), ['title' => 'Tetap Jalan', 'excerpt' => 'R', 'isi_teks' => 'I', 'tanggal' => '2026-10-29', 'kategori' => 'Kegiatan', 'status' => 'draf'])
            ->assertRedirect(route('admin.berita.index'));

        $this->assertSame(1, Berita::count());
    }

    // ---- Tampilan log ----

    public function test_log_tampil_di_halaman_akun_hanya_untuk_super_admin(): void
    {
        $this->masukSebagaiAdmin();
        $this->post(route('admin.berita.store'), ['title' => 'Berita Log', 'excerpt' => 'R', 'isi_teks' => 'I', 'tanggal' => '2026-10-29', 'kategori' => 'Kegiatan', 'status' => 'draf']);

        $this->get(route('admin.akun.index'))->assertOk()->assertSee('Log Aktivitas')->assertSee('Berita dibuat')->assertSee('Berita Log')->assertSee('127.0.0.1');

        $this->actingAs(User::factory()->denganModul(Modul::cases())->create());
        $this->get(route('admin.akun.index'))->assertForbidden();
    }

    public function test_log_dipaginasi_20_per_halaman_terbaru_dulu(): void
    {
        $this->masukSebagaiAdmin();
        foreach (range(1, 35) as $i) {
            LogAktivitas::factory()->create(['aksi' => 'masuk', 'keterangan' => 'Entri nomor '.$i]);
        }

        $this->get(route('admin.akun.index'))->assertSee('Entri nomor 35')->assertSee('Entri nomor 16')->assertDontSee('Entri nomor 15<', false)->assertSee('halaman_log=2', false);
        $this->get(route('admin.akun.index', ['halaman_log' => 2]))->assertSee('Entri nomor 15')->assertSee('Entri nomor 1<', false)->assertDontSee('Entri nomor 35');
    }

    // ---- Data master ----

    public function test_data_master_hanya_untuk_super_admin(): void
    {
        $this->actingAs(User::factory()->denganModul(Modul::cases())->create());

        $this->get(route('admin.master'))->assertForbidden();
        $this->post(route('admin.master.store', 'dusun'), ['nama' => 'Dusun 9'])->assertForbidden();
        $this->assertSame(0, Dusun::count());

        auth()->logout();
        $this->get(route('admin.master'))->assertRedirect(route('admin.login'));
    }

    public function test_data_master_tambah_ubah_hapus_dusun_dan_jabatan(): void
    {
        $this->masukSebagaiAdmin();

        $this->get(route('admin.master'))->assertOk()->assertSee('Data Master')->assertSee('Belum ada data');

        $this->post(route('admin.master.store', 'dusun'), ['nama' => 'Dusun 1'])->assertRedirect(route('admin.master'));
        $this->post(route('admin.master.store', 'dusun'), ['nama' => 'Dusun 2'])->assertRedirect();
        $this->post(route('admin.master.store', 'jabatan'), ['nama' => 'Kepala Desa'])->assertRedirect();
        $this->assertSame([1, 2], Dusun::orderBy('urutan')->pluck('urutan')->all());

        $dusun = Dusun::where('nama', 'Dusun 2')->sole();
        $this->put(route('admin.master.update', ['dusun', $dusun->id]), ['nama' => 'Dusun Dua', 'urutan' => 7])->assertRedirect();
        $this->assertSame(['Dusun Dua', 7], [$dusun->fresh()->nama, $dusun->fresh()->urutan]);
        $this->get(route('admin.master'))->assertSee('Dusun Dua')->assertSee('Kepala Desa');

        $this->delete(route('admin.master.destroy', ['dusun', $dusun->id]))->assertRedirect();
        $this->assertSame(1, Dusun::count());
        $this->assertSame(['master.dibuat', 'master.dibuat', 'master.dibuat', 'master.diubah', 'master.dihapus'], $this->aksi());
    }

    public function test_data_master_menolak_nama_ganda_kosong_dan_jenis_tak_dikenal(): void
    {
        $this->masukSebagaiAdmin();
        Dusun::factory()->create(['nama' => 'Dusun 1']);
        Jabatan::factory()->create(['nama' => 'Kepala Desa']);

        $this->from(route('admin.master'))->post(route('admin.master.store', 'dusun'), ['nama' => 'Dusun 1'])->assertSessionHasErrors('nama');
        $this->post(route('admin.master.store', 'jabatan'), ['nama' => ''])->assertSessionHasErrors('nama');
        $this->post(route('admin.master.store', 'jabatan'), ['nama' => str_repeat('x', 101)])->assertSessionHasErrors('nama');
        $this->put(route('admin.master.update', ['dusun', Dusun::first()->id]), ['nama' => 'Dusun 1', 'urutan' => 5])->assertSessionDoesntHaveErrors();
        $this->put(route('admin.master.update', ['dusun', Dusun::first()->id]), ['nama' => 'Dusun 1', 'urutan' => 5000])->assertSessionHasErrors('urutan');
        $this->post('/admin/data-master/lainnya', ['nama' => 'X'])->assertNotFound();
        $this->delete(route('admin.master.destroy', ['dusun', 9999]))->assertNotFound();
    }

    public function test_jabatan_yang_dipakai_perangkat_tidak_dapat_dihapus(): void
    {
        $this->masukSebagaiAdmin();
        $jabatan = Jabatan::factory()->create(['nama' => 'Sekretaris']);
        PerangkatDesa::factory()->create(['jabatan_id' => $jabatan->id]);

        $this->delete(route('admin.master.destroy', ['jabatan', $jabatan->id]))
            ->assertRedirect(route('admin.master'))->assertSessionHas('toast', fn (string $t) => str_contains($t, 'masih dipakai'));

        $this->assertNotNull($jabatan->fresh());
    }

    public function test_data_master_dipakai_formulir_admin(): void
    {
        $this->masukSebagaiAdmin();
        $this->post(route('admin.master.store', 'jabatan'), ['nama' => 'Kaur Baru'])->assertRedirect();

        $this->get(route('admin.struktur'))->assertOk()->assertSee('Jabatan');
    }

    // ---- Kebersihan kode ----

    public function test_tidak_ada_referensi_demo_atau_todo_backend_tersisa(): void
    {
        $tersisa = [];
        foreach ([app_path(), resource_path(), base_path('routes'), database_path('seeders')] as $folder) {
            foreach (File::allFiles($folder) as $berkas) {
                $isi = $berkas->getContents();
                if (preg_match('/Demo(Content|Store)|TODO\(backend\)|demo_peran|\?demo=/', $isi)) {
                    $tersisa[] = $berkas->getRelativePathname();
                }
            }
        }

        $this->assertSame([], $tersisa, 'Referensi data demo/TODO(backend) masih ada.');
        $this->assertFileDoesNotExist(app_path('Support/DemoContent.php'));
        $this->assertFileDoesNotExist(app_path('Support/DemoStore.php'));
        $this->assertFileDoesNotExist(app_path('Support/DemoStoreKoleksi.php'));
    }

    public function test_profil_kosong_aman_walau_data_master_belum_ada(): void
    {
        $this->assertSame(0, ProfilDesa::count());
        $this->get('/')->assertOk();
        $this->assertSame(StatusAspirasi::Baru->value, 'baru');
    }
}
