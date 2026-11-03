<?php

namespace Tests\Feature;

use App\Enums\Modul;
use App\Models\Agenda;
use App\Models\ApbdesDokumen;
use App\Models\ApbdesPeriode;
use App\Models\ApbdesRincian;
use App\Models\Aspirasi;
use App\Models\Berita;
use App\Models\Dusun;
use App\Models\Jabatan;
use App\Models\KelompokTani;
use App\Models\Komoditas;
use App\Models\PerangkatDesa;
use App\Models\Umkm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Routing\Route as RuteLaravel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Vite;
use Tests\TestCase;

/**
 * Pengujian keamanan akhir (planBE Fase 11): akses per peran ke setiap rute admin, XSS pada semua input teks,
 * CSRF, pencarian dengan karakter khusus (SQL injection), sesi, dan jumlah query.
 */
class Fase11KeamananAkhirTest extends TestCase
{
    use RefreshDatabase;

    private const XSS = '<script>alert(1)</script>"><img src=x onerror=alert(1)>';

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Storage::fake('local');
        Vite::useHotFile(storage_path('framework/testing/vite-hot-tidak-ada'));
    }

    /**
     * Semua rute di bawah /admin yang membutuhkan login, beserta modul yang melindunginya.
     *
     * @return list<array{0: RuteLaravel, 1: string|null}>
     */
    private function ruteAdmin(): array
    {
        $hasil = [];
        foreach (Route::getRoutes() as $rute) {
            $nama = (string) $rute->getName();
            if (! str_starts_with($nama, 'admin.') || str_contains($nama, 'login') || str_contains($nama, 'password.')) {
                continue;
            }
            $modul = collect($rute->gatherMiddleware())->first(fn ($m) => is_string($m) && str_starts_with($m, 'modul:'));
            $hasil[] = [$rute, $modul ? substr($modul, 6) : null];
        }

        return $hasil;
    }

    private function url(RuteLaravel $rute): string
    {
        $isi = ['id' => 1, 'user' => 1, 'dokumen' => 1, 'slug' => 'tidak-ada', 'tab' => 'sejarah', 'jenis' => 'dusun'];

        return url(preg_replace_callback('/\{(\w+)\??\}/', fn ($m) => (string) ($isi[$m[1]] ?? 1), $rute->uri()));
    }

    private function panggil(RuteLaravel $rute)
    {
        $metode = collect($rute->methods())->first(fn ($m) => ! in_array($m, ['HEAD'], true));

        return $this->call($metode, $this->url($rute), $metode === 'GET' ? [] : ['_method' => $metode]);
    }

    // ---- Akses per peran ----

    public function test_seluruh_rute_admin_terdeteksi(): void
    {
        $this->assertGreaterThan(40, count($this->ruteAdmin()));
        $modul = array_unique(array_filter(array_column($this->ruteAdmin(), 1)));
        sort($modul);
        $this->assertSame(['akun', 'apbdes', 'aspirasi', 'berita', 'potensi', 'profil'], array_values($modul));
    }

    public function test_tamu_diarahkan_ke_login_untuk_setiap_rute_admin(): void
    {
        foreach ($this->ruteAdmin() as [$rute]) {
            $respons = $this->panggil($rute);
            $this->assertSame(302, $respons->getStatusCode(), $rute->getName().' tidak diarahkan');
            $this->assertStringEndsWith('/admin/masuk', (string) $respons->headers->get('Location'), $rute->getName());
        }
    }

    public function test_staf_tanpa_penugasan_ditolak_403_di_setiap_rute_modul(): void
    {
        $staf = User::factory()->create();

        foreach ($this->ruteAdmin() as [$rute, $modul]) {
            $this->actingAs($staf);
            $status = $this->panggil($rute)->getStatusCode();
            if ($modul === null) {
                $this->assertNotSame(403, $status, $rute->getName().' (tanpa modul) seharusnya terbuka');

                continue;
            }
            $this->assertSame(403, $status, $rute->getName().' (modul '.$modul.') bocor ke staf tanpa penugasan');
        }
    }

    public function test_staf_hanya_menembus_rute_modul_yang_ditugaskan(): void
    {
        foreach (['berita', 'potensi', 'profil', 'apbdes', 'aspirasi'] as $dimiliki) {
            $staf = User::factory()->denganModul([Modul::from($dimiliki)])->create();

            foreach ($this->ruteAdmin() as [$rute, $modul]) {
                if ($modul === null) {
                    continue;
                }
                $this->actingAs($staf);
                $status = $this->panggil($rute)->getStatusCode();
                $this->assertSame($modul === $dimiliki ? true : false, $status !== 403, "{$rute->getName()} dengan modul {$dimiliki}: status {$status}");
            }
        }
    }

    public function test_modul_akun_dan_data_master_tertutup_untuk_staf_walau_semua_modul_ditugaskan(): void
    {
        $staf = User::factory()->denganModul(Modul::cases())->create();

        foreach ($this->ruteAdmin() as [$rute, $modul]) {
            if ($modul === 'akun') {
                $this->actingAs($staf);
                $this->assertSame(403, $this->panggil($rute)->getStatusCode(), $rute->getName());
            }
        }
    }

    public function test_super_admin_tidak_pernah_mendapat_403_atau_galat_server(): void
    {
        Jabatan::factory()->create();
        $super = User::factory()->superAdmin()->create();

        foreach ($this->ruteAdmin() as [$rute]) {
            $this->actingAs($super);
            $status = $this->panggil($rute)->getStatusCode();
            $this->assertNotSame(403, $status, $rute->getName());
            $this->assertLessThan(500, $status, $rute->getName().' menghasilkan galat server');
        }
    }

    public function test_akun_nonaktif_ditolak_di_setiap_rute_walau_sesi_masih_ada(): void
    {
        $this->actingAs(User::factory()->nonaktif()->superAdmin()->create());

        foreach ($this->ruteAdmin() as [$rute]) {
            $this->assertSame(302, $this->panggil($rute)->getStatusCode(), $rute->getName());
        }
        $this->assertGuest();
    }

    // ---- CSRF ----

    public function test_csrf_ditegakkan_pada_formulir_publik_dan_admin(): void
    {
        // Pada lingkungan "testing" CSRF dimatikan oleh framework; di sini lingkungan dibuat seperti lokal/produksi.
        $this->app->detectEnvironment(fn () => 'local');
        User::factory()->create(['email' => 'a@desa.test']);

        $this->post(route('admin.login.store'), ['login' => 'a@desa.test', 'password' => 'password'])->assertStatus(419);
        $this->post(route('aspirasi.store'), ['nama' => 'X', 'pesan' => str_repeat('a', 30)])->assertStatus(419);
        $this->post(route('admin.password.email'), ['email' => 'a@desa.test'])->assertStatus(419);

        $this->actingAs(User::factory()->superAdmin()->create());
        $this->post(route('admin.berita.store'), [])->assertStatus(419);
        $this->delete(route('admin.umkm.destroy', 1))->assertStatus(419);
        $this->post(route('admin.logout'))->assertStatus(419);
        $this->assertAuthenticated();
    }

    public function test_csrf_dengan_token_sah_diterima(): void
    {
        $this->app->detectEnvironment(fn () => 'local');
        User::factory()->create(['email' => 'a@desa.test']);

        $this->withSession(['_token' => 'token-uji'])
            ->post(route('admin.login.store'), ['_token' => 'token-uji', 'login' => 'a@desa.test', 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));
    }

    // ---- Proksi tepercaya ----

    public function test_proksi_tepercaya_dibaca_lewat_config_agar_aman_dengan_config_cache(): void
    {
        $this->assertNull(config('desa.proksi_tepercaya'));
        $this->assertStringNotContainsString('env(', file_get_contents(base_path('bootstrap/app.php')), 'env() di luar config tidak terbaca saat config:cache');

        $_ENV['TRUSTED_PROXIES'] = '*';
        $_SERVER['TRUSTED_PROXIES'] = '*';
        try {
            $this->refreshApplication();
            $this->assertSame('*', config('desa.proksi_tepercaya'));

            $respons = $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.5'])
                ->get('/', ['X-Forwarded-Proto' => 'https', 'X-Forwarded-For' => '203.0.113.9', 'X-Forwarded-Port' => '443']);
            $this->assertStringContainsString('max-age=', (string) $respons->headers->get('Strict-Transport-Security'));
        } finally {
            unset($_ENV['TRUSTED_PROXIES'], $_SERVER['TRUSTED_PROXIES']);
            TrustProxies::flushState();
        }
    }

    // ---- Sesi ----

    public function test_id_sesi_diganti_saat_login_dan_dihapus_saat_logout(): void
    {
        User::factory()->create(['email' => 'a@desa.test']);
        $this->get(route('admin.login'));
        $sebelum = session()->getId();

        $this->post(route('admin.login.store'), ['login' => 'a@desa.test', 'password' => 'password']);
        $this->assertNotSame($sebelum, session()->getId());
        $this->assertAuthenticated();

        $this->post(route('admin.logout'));
        $this->assertGuest();
        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
    }

    // ---- Brute force ----

    public function test_penguncian_login_per_akun_dan_ip_tidak_menghalangi_akun_lain(): void
    {
        User::factory()->create(['email' => 'korban@desa.test']);
        User::factory()->create(['email' => 'lain@desa.test']);

        for ($i = 0; $i < 6; $i++) {
            $this->post(route('admin.login.store'), ['login' => 'korban@desa.test', 'password' => 'tebakan'.$i]);
        }
        $this->post(route('admin.login.store'), ['login' => 'korban@desa.test', 'password' => 'password'])->assertSessionHas('gagal_masuk');
        $this->assertGuest();

        $this->post(route('admin.login.store'), ['login' => 'lain@desa.test', 'password' => 'password'])->assertRedirect(route('admin.dashboard'));
    }

    public function test_penguncian_tidak_membedakan_akun_ada_dan_tidak_ada(): void
    {
        User::factory()->create(['email' => 'ada@desa.test']);

        $pesan = [];
        foreach (['ada@desa.test', 'hantu@desa.test'] as $login) {
            for ($i = 0; $i < 5; $i++) {
                $this->post(route('admin.login.store'), ['login' => $login, 'password' => 'salah']);
            }
            $pesan[$login] = session('gagal_masuk');
            $this->post(route('admin.login.store'), ['login' => $login, 'password' => 'salah']);
            $pesan[$login.'-kunci'] = preg_replace('/\d+/', 'N', (string) session('gagal_masuk'));
        }

        $this->assertSame($pesan['ada@desa.test'], $pesan['hantu@desa.test']);
        $this->assertSame($pesan['ada@desa.test-kunci'], $pesan['hantu@desa.test-kunci']);
    }

    public function test_permintaan_tautan_atur_ulang_dibatasi(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('admin.password.email'), ['email' => 'siapa@desa.test'])->assertRedirect();
        }
        $this->post(route('admin.password.email'), ['email' => 'siapa@desa.test'])->assertStatus(429);
    }

    // ---- SQL injection / karakter khusus ----

    public function test_pencarian_dengan_karakter_khusus_tidak_menimbulkan_galat_di_semua_halaman(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());
        Berita::factory()->create(['judul' => 'Berita Aman']);
        Umkm::factory()->create(['nama' => 'Usaha Aman']);
        Aspirasi::factory()->create(['nama' => 'Warga Aman']);
        Komoditas::factory()->create(['nama' => 'Jagung Aman']);
        KelompokTani::factory()->create(['nama' => 'Kelompok Aman']);
        Agenda::factory()->create(['judul' => 'Agenda Aman']);

        $payload = ["' OR '1'='1", '" OR ""="', "'; DROP TABLE berita; --", '%', '_', '\\', '%%', "1' UNION SELECT * FROM users --", '${7*7}', '{{7*7}}', str_repeat('A', 500)];
        $halaman = [
            ['berita.index', 'q'], ['potensi', 'q'],
            ['admin.berita.index', 'q'], ['admin.agenda.index', 'q'], ['admin.pertanian', 'q'], ['admin.kelompok-tani', 'q'],
            ['admin.umkm', 'q'], ['admin.aspirasi.index', 'q'],
        ];

        foreach ($halaman as [$nama, $param]) {
            foreach ($payload as $kata) {
                $respons = $this->get(route($nama, [$param => $kata]));
                $this->assertSame(200, $respons->getStatusCode(), "{$nama} dengan q=".substr(json_encode($kata), 0, 40));
                $respons->assertDontSee('Berita Aman')->assertDontSee('Usaha Aman')->assertDontSee('Warga Aman');
            }
        }

        // Kata kunci yang hanya berisi karakter NUL dipangkas menjadi kosong: halaman tetap normal (tanpa galat).
        foreach (['berita.index', 'potensi', 'admin.berita.index', 'admin.aspirasi.index'] as $nama) {
            $this->get(route($nama, ['q' => "\0"]))->assertOk();
        }

        foreach (['kategori', 'status', 'tahun', 'page', 'tab', 'waktu', 'ubah', 'id', 'periode'] as $param) {
            foreach (["' OR 1=1 --", '-1', '99999999999999999999', '[]'] as $nilai) {
                foreach (['berita.index', 'apbdes', 'potensi', 'admin.berita.index', 'admin.agenda.index', 'admin.apbdes', 'admin.aspirasi.index', 'admin.profil', 'admin.pertanian', 'admin.akun.index'] as $nama) {
                    $this->assertLessThan(500, $this->get(route($nama, [$param => $nilai]))->getStatusCode(), "{$nama}?{$param}=".$nilai);
                }
            }
        }

        $this->assertSame(1, Berita::count());
        $this->assertGreaterThan(0, DB::table('users')->count());
    }

    public function test_slug_dan_id_aneh_menghasilkan_404_bukan_galat(): void
    {
        foreach (['../../etc/passwd', "' OR 1=1", '%00', str_repeat('a', 300)] as $slug) {
            $this->assertContains($this->get('/berita/'.rawurlencode($slug))->getStatusCode(), [404]);
        }
        $this->get('/transparansi-apbdes/dokumen/abc')->assertNotFound();
    }

    // ---- XSS pada semua input teks ----

    public function test_input_teks_di_semua_modul_selalu_di_escape_di_admin_dan_publik(): void
    {
        $x = self::XSS;
        $admin = User::factory()->superAdmin()->create();
        $this->actingAs($admin);
        Jabatan::factory()->create(['nama' => 'Kaur']);
        $dusun = Dusun::factory()->create();

        $this->post(route('admin.berita.store'), ['title' => $x, 'excerpt' => $x, 'isi_teks' => $x."\n\n[a](javascript:alert(1))", 'tanggal' => '2026-10-29', 'kategori' => 'Kegiatan', 'status' => 'terbit', 'aksi' => 'terbit']);
        $this->post(route('admin.agenda.store'), ['title' => $x, 'tanggal' => now()->addDay()->toDateString(), 'lokasi' => $x, 'deskripsi' => $x, 'status' => 'terbit']);
        $this->put(route('admin.profil.simpan', 'sejarah'), ['sejarah' => $x]);
        $this->put(route('admin.profil.simpan', 'visi'), ['visi' => $x, 'misi' => [$x]]);
        $this->put(route('admin.profil.simpan', 'lokasi'), ['alamat' => $x, 'lintang' => 1, 'bujur' => 1, 'telepon' => '0721123456', 'terverifikasi' => '1', 'aksi' => 'terbit']);
        $this->put(route('admin.profil.simpan', 'data'), ['penduduk' => 1, 'kk' => 1, 'luas' => 1, 'dusun' => 1, 'sumber' => $x, 'terverifikasi' => '1']);
        $this->post(route('admin.struktur.store'), ['nama' => $x, 'jabatan' => 'Kaur', 'tampil' => '1']);
        $this->post(route('admin.pertanian.store'), ['nama' => $x, 'musim_panen' => $x, 'deskripsi' => $x, 'status' => 'terbit']);
        $komoditas = Komoditas::query()->first();
        $this->post(route('admin.kelompok-tani.store'), ['nama' => $x, 'ketua' => $x, 'komoditas' => $komoditas?->id, 'dusun' => $dusun->id]);
        $this->post(route('admin.umkm.store'), ['nama' => $x, 'produk' => $x, 'deskripsi' => $x, 'status' => 'terbit']);
        $this->post(route('admin.apbdes.store'), ['tahun' => 2030, 'periode' => $x, 'status' => 'terbit', 'aksi' => 'terbit', 'rincian' => ['pendapatan' => [['uraian' => $x, 'anggaran' => '1000']]]]);
        $periode = ApbdesPeriode::query()->first();
        ApbdesDokumen::factory()->create(['periode_id' => $periode?->id ?? 1, 'judul' => $x, 'keterangan_periode' => $x]);
        $this->post(route('admin.akun.store'), ['nama' => $x, 'email' => 'xss@desa.test', 'peran' => 'staf']);
        $this->post(route('admin.master.store', 'dusun'), ['nama' => $x]);
        Aspirasi::factory()->create(['nama' => $x, 'pesan' => $x]);

        $this->assertGreaterThanOrEqual(1, Berita::count() + Umkm::count() + PerangkatDesa::count(), 'Tidak ada data uji yang tersimpan.');

        $halaman = [
            '/', '/profil-desa', '/transparansi-apbdes', '/potensi-desa', '/berita', '/berita/'.Berita::value('slug'), '/kontak-lokasi',
            '/admin', '/admin/berita', '/admin/agenda?waktu=semua', '/admin/profil', '/admin/profil?tab=visi', '/admin/profil?tab=lokasi', '/admin/profil?tab=data',
            '/admin/struktur-perangkat', '/admin/potensi-pertanian', '/admin/kelompok-tani', '/admin/umkm', '/admin/apbdes', '/admin/aspirasi', '/admin/akun', '/admin/data-master',
        ];
        $ubah = array_filter([
            Berita::value('slug') ? '/admin/berita/'.Berita::value('slug').'/ubah' : null,
            Agenda::value('id') ? '/admin/agenda?waktu=semua&ubah='.Agenda::value('id') : null,
            PerangkatDesa::value('id') ? '/admin/struktur-perangkat?ubah='.PerangkatDesa::value('id') : null,
            Aspirasi::value('id') ? '/admin/aspirasi?id='.Aspirasi::value('id') : null,
        ]);

        foreach ([...$halaman, ...$ubah] as $url) {
            $html = $this->get($url)->assertStatus(200)->getContent();
            $this->assertStringNotContainsString('<script>alert(1)</script>', $html, "Skrip mentah di {$url}");
            $this->assertStringNotContainsString('<img src=x', $html, "Tag img mentah di {$url}");

            // Periksa DOM sebenarnya: tidak boleh ada atribut event (on*) atau skrip inline pada elemen mana pun.
            $dom = new \DOMDocument;
            @$dom->loadHTML('<?xml encoding="utf-8"?>'.$html);
            $xpath = new \DOMXPath($dom);
            $this->assertSame(0, $xpath->query('//@*[starts-with(name(), "on")]')->length, "Atribut event di {$url}");
            $this->assertSame(0, $xpath->query('//script[not(@src)]')->length, "Skrip inline di {$url}");
            // Teks sumber di dalam <textarea> editor boleh memuat "javascript:" karena di-escape; yang dilarang adalah tautan/atribut aktifnya.
            $this->assertSame(0, $xpath->query('//@href[starts-with(translate(normalize-space(.), "JAVASCRIPT", "javascript"), "javascript:")] | //@src[starts-with(translate(normalize-space(.), "JAVASCRIPT", "javascript"), "javascript:")] | //@action[starts-with(translate(normalize-space(.), "JAVASCRIPT", "javascript"), "javascript:")]')->length, "Tautan javascript aktif di {$url}");
            $this->assertSame(0, $xpath->query('//img[contains(@src, "x") and @onerror]')->length, "Gambar jebakan di {$url}");
        }
    }

    // ---- Performa (N+1) ----

    public function test_jumlah_query_halaman_tidak_bertambah_seiring_data_termasuk_apbdes_dan_admin(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());

        $hitung = function (string $url): int {
            DB::flushQueryLog();
            DB::enableQueryLog();
            $this->get($url)->assertOk();
            $jumlah = count(DB::getQueryLog());
            DB::disableQueryLog();

            return $jumlah;
        };
        $halaman = ['/', '/berita', '/potensi-desa', '/profil-desa', '/transparansi-apbdes', '/admin', '/admin/berita', '/admin/agenda?waktu=semua', '/admin/struktur-perangkat', '/admin/potensi-pertanian', '/admin/kelompok-tani', '/admin/umkm', '/admin/aspirasi', '/admin/akun', '/admin/data-master', '/admin/apbdes'];

        $isi = function (int $n) {
            $periode = ApbdesPeriode::factory()->create();
            ApbdesRincian::factory()->count($n)->create(['periode_id' => $periode->id]);
            ApbdesDokumen::factory()->count($n)->create(['periode_id' => $periode->id]);
            Berita::factory()->count($n)->create();
            Agenda::factory()->count($n)->create();
            Umkm::factory()->count($n)->create();
            Aspirasi::factory()->count($n)->create();
            $dusun = Dusun::factory()->create();
            KelompokTani::factory()->count($n)->create(['dusun_id' => $dusun->id, 'komoditas_id' => Komoditas::factory()]);
            PerangkatDesa::factory()->count($n)->create();
            User::factory()->count($n)->create();
        };

        $isi(3);
        $awal = array_map($hitung, $halaman);
        $isi(12);
        $akhir = array_map($hitung, $halaman);

        foreach ($halaman as $i => $url) {
            $this->assertLessThanOrEqual($awal[$i], $akhir[$i], "{$url}: query bertambah ({$awal[$i]} → {$akhir[$i]}), kemungkinan N+1");
        }
    }
}
