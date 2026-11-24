<?php

namespace Tests\Feature;

use App\Enums\Modul;
use App\Models\Berita;
use App\Models\StatistikKunjungan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Vite;
use Tests\TestCase;

/**
 * Statistik pengunjung internal: hanya agregat harian per halaman, tanpa IP/user agent di basis data,
 * pengunjung unik per hari lewat hash di cache, dan tanpa cookie baru.
 */
class StatistikPengunjungTest extends TestCase
{
    use RefreshDatabase;

    private const PERAMBAN = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0 Safari/537.36';

    protected function setUp(): void
    {
        parent::setUp();
        // Jangan bergantung pada server dev Vite yang mungkin sedang berjalan (public/hot).
        Vite::useHotFile(storage_path('framework/testing/vite-hot-tidak-ada'));
    }

    private function kunjungi(string $url, string $agen = self::PERAMBAN, string $ip = '203.0.113.10', array $header = [])
    {
        return $this->withHeaders(['User-Agent' => $agen] + $header)->withServerVariables(['REMOTE_ADDR' => $ip])->get($url);
    }

    private function baris(string $path): ?StatistikKunjungan
    {
        return StatistikKunjungan::query()->where('tanggal', now()->toDateString())->where('path', $path)->first();
    }

    // ---- Pencatatan ----

    public function test_kunjungan_halaman_publik_menambah_angka_harian(): void
    {
        $this->kunjungi('/profil-desa')->assertOk();
        $this->kunjungi('/profil-desa', ip: '203.0.113.11')->assertOk();

        $this->assertSame(2, $this->baris('/profil-desa')->jumlah_tayang);
        $this->assertSame(2, $this->baris('/profil-desa')->jumlah_pengunjung);
        $this->assertSame(2, $this->baris(StatistikKunjungan::SEMUA)->jumlah_tayang);
        $this->assertSame(2, $this->baris(StatistikKunjungan::SEMUA)->jumlah_pengunjung);
    }

    public function test_beranda_dicatat_dengan_path_slash(): void
    {
        $this->kunjungi('/')->assertOk();

        $this->assertSame(1, $this->baris('/')->jumlah_tayang);
    }

    public function test_berita_dicatat_per_slug_tanpa_query_string(): void
    {
        $berita = Berita::factory()->create();

        $this->kunjungi('/berita/'.$berita->slug.'?utm_source=wa')->assertOk();

        $this->assertSame(1, $this->baris('/berita/'.$berita->slug)->jumlah_tayang);
        $this->assertSame(1, StatistikKunjungan::query()->where('path', '!=', StatistikKunjungan::SEMUA)->count());
    }

    public function test_pengunjung_yang_sama_di_hari_yang_sama_dihitung_sekali_tetapi_tayangan_bertambah(): void
    {
        $this->kunjungi('/profil-desa');
        $this->kunjungi('/profil-desa');
        $this->kunjungi('/potensi-desa');

        $this->assertSame(2, $this->baris('/profil-desa')->jumlah_tayang);
        $this->assertSame(1, $this->baris('/profil-desa')->jumlah_pengunjung);
        $this->assertSame(1, $this->baris('/potensi-desa')->jumlah_pengunjung);
        $this->assertSame(3, $this->baris(StatistikKunjungan::SEMUA)->jumlah_tayang);
        $this->assertSame(1, $this->baris(StatistikKunjungan::SEMUA)->jumlah_pengunjung);
    }

    public function test_pengunjung_berbeda_dihitung_terpisah(): void
    {
        $this->kunjungi('/profil-desa', ip: '203.0.113.20');
        $this->kunjungi('/profil-desa', agen: self::PERAMBAN.' Edg/130.0', ip: '203.0.113.20');

        $this->assertSame(2, $this->baris(StatistikKunjungan::SEMUA)->jumlah_pengunjung);
    }

    public function test_pengunjung_yang_sama_dihitung_lagi_pada_hari_berikutnya(): void
    {
        $this->kunjungi('/profil-desa');

        $this->travel(1)->day();
        $this->kunjungi('/profil-desa');

        $this->assertSame(2, StatistikKunjungan::query()->where('path', StatistikKunjungan::SEMUA)->sum('jumlah_pengunjung'));
        $this->assertSame(2, StatistikKunjungan::query()->where('path', StatistikKunjungan::SEMUA)->count());
    }

    // ---- Yang tidak dihitung ----

    public function test_admin_tidak_dihitung(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());

        $this->kunjungi('/admin')->assertOk();
        $this->kunjungi('/admin/statistik-pengunjung')->assertOk();
        $this->kunjungi('/admin/masuk');

        $this->assertSame(0, StatistikKunjungan::count());
    }

    public function test_bot_dan_pratinjau_tautan_tidak_dihitung(): void
    {
        foreach ([
            'Googlebot/2.1 (+http://www.google.com/bot.html)',
            'Mozilla/5.0 (compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)',
            'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)',
            'WhatsApp/2.23.20.0 A',
            'Slackbot-LinkExpanding 1.0',
            'curl/8.4.0',
            'Mozilla/5.0 (compatible; Yahoo! Slurp; http://help.yahoo.com/help/us/ysearch/slurp)',
            'Twitterbot/1.0',
            '',
        ] as $agen) {
            $this->kunjungi('/', $agen)->assertOk();
        }

        $this->assertSame(0, StatistikKunjungan::count());
    }

    public function test_permintaan_ajax_dan_prefetch_tidak_dihitung(): void
    {
        $this->kunjungi('/', header: ['X-Requested-With' => 'XMLHttpRequest']);
        $this->kunjungi('/', header: ['Purpose' => 'prefetch']);
        $this->kunjungi('/', header: ['Sec-Purpose' => 'prefetch;prerender']);
        $this->kunjungi('/', header: ['X-Moz' => 'prefetch']);

        $this->assertSame(0, StatistikKunjungan::count());
    }

    public function test_aset_sitemap_robots_dan_halaman_tidak_ada_tidak_dihitung(): void
    {
        $this->kunjungi('/sitemap.xml')->assertOk();
        $this->kunjungi('/robots.txt')->assertOk();
        $this->kunjungi('/favicon.svg');
        $this->kunjungi('/build/assets/tidak-ada.js')->assertNotFound();
        $this->kunjungi('/halaman-yang-tidak-ada')->assertNotFound();
        $this->kunjungi('/berita/slug-yang-tidak-ada')->assertNotFound();

        $this->assertSame(0, StatistikKunjungan::count());
    }

    public function test_permintaan_selain_get_tidak_dihitung(): void
    {
        $this->withHeaders(['User-Agent' => self::PERAMBAN])->post('/ruang-aspirasi', []);

        $this->assertSame(0, StatistikKunjungan::count());
    }

    public function test_dimatikan_lewat_config_tidak_mencatat(): void
    {
        config(['desa.statistik.aktif' => false]);

        $this->kunjungi('/')->assertOk();

        $this->assertSame(0, StatistikKunjungan::count());
    }

    // ---- Privasi dan ketahanan ----

    public function test_tabel_tidak_punya_kolom_ip_user_agent_atau_hash(): void
    {
        $kolom = Schema::getColumnListing('statistik_kunjungan');

        $this->assertEqualsCanonicalizing(['id', 'tanggal', 'path', 'jumlah_tayang', 'jumlah_pengunjung'], $kolom);
    }

    public function test_ip_dan_user_agent_tidak_masuk_basis_data(): void
    {
        $this->kunjungi('/', ip: '203.0.113.77');

        $isi = json_encode(StatistikKunjungan::all()->toArray());
        $this->assertStringNotContainsString('203.0.113.77', $isi);
        $this->assertStringNotContainsString('Mozilla', $isi);
    }

    public function test_pencatatan_tidak_menambah_cookie_baru(): void
    {
        $nama = collect($this->kunjungi('/')->headers->getCookies())->map->getName()->all();

        foreach ($nama as $cookie) {
            $this->assertMatchesRegularExpression('/session$|^XSRF-TOKEN$/i', $cookie, "Cookie tak terduga: {$cookie}");
        }
    }

    public function test_kegagalan_pencatatan_tidak_membuat_halaman_galat(): void
    {
        Schema::drop('statistik_kunjungan');

        $this->kunjungi('/profil-desa')->assertOk();
    }

    // ---- Akses admin ----

    public function test_halaman_statistik_admin_butuh_login_dan_hak_akses(): void
    {
        $this->get(route('admin.statistik'))->assertRedirect(route('admin.login'));

        $this->actingAs(User::factory()->denganModul([Modul::Berita])->create());
        $this->get(route('admin.statistik'))->assertForbidden();
    }

    public function test_staf_dengan_modul_statistik_dan_super_admin_dapat_membuka_halaman(): void
    {
        $this->kunjungi('/profil-desa');
        $this->kunjungi('/profil-desa', ip: '203.0.113.50');

        $this->actingAs(User::factory()->denganModul([Modul::Statistik])->create());
        $this->get(route('admin.statistik'))->assertOk()
            ->assertSee('Statistik Pengunjung')
            ->assertSee('Pengunjung 30 hari terakhir')
            ->assertSee('10 halaman terpopuler')
            ->assertSee('/profil-desa');

        $this->actingAs(User::factory()->superAdmin()->create());
        $this->get(route('admin.statistik'))->assertOk();
    }

    public function test_dashboard_menampilkan_kartu_hanya_untuk_yang_berhak(): void
    {
        $this->kunjungi('/');

        $this->actingAs(User::factory()->denganModul([Modul::Statistik])->create());
        $this->get(route('admin.dashboard'))->assertOk()->assertSee('Pengunjung situs')->assertSee('Grafik pengunjung 14 hari terakhir');

        $this->actingAs(User::factory()->denganModul([Modul::Berita])->create());
        $this->get(route('admin.dashboard'))->assertOk()->assertDontSee('Pengunjung situs')->assertDontSee('Grafik pengunjung');
    }

    public function test_menu_statistik_hanya_tampil_untuk_yang_berhak(): void
    {
        $this->actingAs(User::factory()->denganModul([Modul::Berita])->create());
        $this->get(route('admin.dashboard'))->assertDontSee(route('admin.statistik'), false);

        $this->actingAs(User::factory()->denganModul([Modul::Statistik])->create());
        $this->get(route('admin.dashboard'))->assertSee(route('admin.statistik'), false);
    }

    // ---- Widget footer publik ----

    /** Nilai angka pada blok statistik footer untuk satu label (dt diikuti dd). */
    private function nilaiFooter(string $html, string $label): ?string
    {
        preg_match('#<dt[^>]*>\s*'.preg_quote($label, '#').'\s*</dt>\s*<dd[^>]*>\s*([\d.]+)\s*</dd>#', $html, $cocok);

        return $cocok[1] ?? null;
    }

    public function test_footer_menampilkan_kartu_statistik_tiga_baris_tanpa_data_30_hari(): void
    {
        $this->kunjungi('/profil-desa');
        $this->kunjungi('/profil-desa', ip: '203.0.113.60');
        StatistikKunjungan::query()->create(['tanggal' => now()->subDay()->toDateString(), 'path' => StatistikKunjungan::SEMUA, 'jumlah_tayang' => 9, 'jumlah_pengunjung' => 7]);
        Cache::forget('statistik.publik');

        $html = $this->get('/kontak-lokasi')->assertOk()->assertSee('Statistik Pengunjung')->getContent();

        $this->assertSame('2', $this->nilaiFooter($html, 'Hari ini'));
        $this->assertSame('7', $this->nilaiFooter($html, 'Kemarin'));
        $this->assertSame('9', $this->nilaiFooter($html, 'Jumlah pengunjung'));
        $this->assertSame(3, substr_count($html, 'border-t border-surface/20 py-2.5'), 'Tiga baris dipisah garis tipis');
        $this->assertStringNotContainsString('30 hari terakhir', $html);
    }

    /** Isi elemen <footer> saja, agar tidak tercampur dengan header. */
    private function footer(string $url = '/kontak-lokasi'): string
    {
        $html = $this->get($url)->assertOk()->getContent();
        $this->assertSame(1, preg_match('#<footer\b.*</footer>#s', $html, $cocok));

        return $cocok[0];
    }

    public function test_footer_desktop_tiga_kolom_identitas_tautan_dan_statistik(): void
    {
        $footer = $this->footer();

        $this->assertStringContainsString('md:grid-cols-12', $footer);
        $this->assertStringContainsString('md:items-start', $footer, 'Kolom rata atas');
        $this->assertStringContainsString('md:col-span-5', $footer);
        $this->assertStringContainsString('md:col-span-3', $footer);
        $this->assertStringContainsString('md:col-span-4', $footer);

        $identitas = strpos($footer, 'Marga Agung');
        $tautan = strpos($footer, 'id="tautan-footer-judul"');
        $statistik = strpos($footer, 'id="statistik-footer-judul"');
        $this->assertTrue($identitas < $tautan && $tautan < $statistik, 'Urutan kolom: identitas, tautan, statistik');
        $this->assertSame(1, substr_count($footer, 'bg-surface/15 text-accent'), 'Satu ikon grafik kecil di judul kartu');
    }

    public function test_footer_memuat_identitas_wilayah_lengkap_tanpa_kontak(): void
    {
        $footer = $this->footer();

        $this->assertStringContainsString('Marga Agung', $footer);
        foreach (['Kecamatan Jati Agung', 'Kabupaten Lampung Selatan', 'Provinsi Lampung'] as $bagian) {
            $this->assertStringContainsString($bagian, $footer);
        }
        $this->assertStringNotContainsString('mailto:', $footer);
        $this->assertStringNotContainsString('tel:', $footer);
        $this->assertStringNotContainsString('wa.me', $footer);
    }

    public function test_footer_memuat_tautan_cepat_dengan_target_sentuh_44px(): void
    {
        $footer = $this->footer();

        $this->assertStringContainsString('>Tautan</h2>', $footer);
        foreach ([
            'Profil Desa' => route('profil'),
            'APBDes' => route('apbdes'),
            'Berita &amp; Agenda' => route('berita.index'),
            'Ruang Aspirasi' => route('aspirasi.create'),
        ] as $label => $alamat) {
            $this->assertMatchesRegularExpression(
                '#<a href="'.preg_quote($alamat, '#').'" class="[^"]*min-h-11[^"]*">'.preg_quote($label, '#').'</a>#',
                $footer,
                "Tautan {$label} tidak ada atau targetnya kurang dari 44 px",
            );
        }
    }

    public function test_di_hp_identitas_dan_tautan_disembunyikan_dan_kartu_statistik_tetap_di_atas(): void
    {
        $footer = $this->footer();

        $this->assertMatchesRegularExpression('#class="hidden [^"]*md:flex[^"]*md:col-span-5"#', $footer, 'Identitas hanya tampil di desktop');
        $this->assertMatchesRegularExpression('#<nav [^>]*class="hidden [^"]*md:flex[^"]*md:col-span-3"#', $footer, 'Tautan cepat hanya tampil di desktop');
        $this->assertLessThan(strpos($footer, 'Seluruh hak dilindungi'), strpos($footer, 'Statistik Pengunjung'), 'Kartu di atas hak cipta');
        $this->assertLessThan(strpos($footer, 'Kebijakan Privasi</a>'), strpos($footer, 'Seluruh hak dilindungi'));
    }

    public function test_baris_bawah_footer_hak_cipta_kiri_dan_kebijakan_privasi_kanan_di_desktop(): void
    {
        $footer = $this->footer();

        $this->assertStringContainsString('md:flex-row md:items-center md:justify-between', $footer);
        $this->assertStringContainsString('md:border-t md:border-surface/20', $footer, 'Garis tipis pemisah di desktop');
        $this->assertStringContainsString('&copy; '.now()->year.' Desa Marga Agung. Seluruh hak dilindungi.', $footer);
    }

    public function test_tanpa_statistik_dua_kolom_lain_melebar_mengisi_ruang(): void
    {
        config(['desa.statistik.tampil_publik' => false]);

        $footer = $this->footer();

        $this->assertStringNotContainsString('id="statistik-footer-judul"', $footer);
        $this->assertStringContainsString('md:col-span-7', $footer);
        $this->assertStringContainsString('md:col-span-5', $footer);
        $this->assertStringNotContainsString('md:col-span-4', $footer);
        $this->assertStringNotContainsString('md:col-span-3', $footer);
        $this->assertStringContainsString('Kebijakan Privasi', $footer);
    }

    public function test_angka_footer_diformat_ribuan(): void
    {
        StatistikKunjungan::query()->create(['tanggal' => now()->toDateString(), 'path' => StatistikKunjungan::SEMUA, 'jumlah_tayang' => 90000, 'jumlah_pengunjung' => 61185]);
        Cache::forget('statistik.publik');

        $html = $this->get('/kontak-lokasi')->assertOk()->getContent();

        $this->assertSame('61.185', $this->nilaiFooter($html, 'Hari ini'));
        $this->assertSame('61.185', $this->nilaiFooter($html, 'Jumlah pengunjung'));
    }

    public function test_footer_tanpa_statistik_hanya_menyisakan_hak_cipta_dan_tautan_kebijakan(): void
    {
        config(['desa.statistik.tampil_publik' => false]);
        $this->get('/kontak-lokasi')->assertOk()
            ->assertDontSee('Statistik Pengunjung')
            ->assertDontSee('Jumlah pengunjung')
            ->assertSee('Seluruh hak dilindungi')
            ->assertSee('Kebijakan Privasi');

        config(['desa.statistik.tampil_publik' => true, 'desa.statistik.aktif' => false]);
        $this->get('/kontak-lokasi')->assertOk()->assertDontSee('Statistik Pengunjung');
    }

    public function test_angka_footer_di_cache_beberapa_menit(): void
    {
        $this->kunjungi('/profil-desa');
        Cache::forget('statistik.publik');
        $this->assertSame('1', $this->nilaiFooter($this->get('/kontak-lokasi')->getContent(), 'Jumlah pengunjung'));

        $this->kunjungi('/profil-desa', ip: '203.0.113.61');
        $this->assertSame('1', $this->nilaiFooter($this->get('/kontak-lokasi')->getContent(), 'Jumlah pengunjung'));

        Cache::forget('statistik.publik');
        $this->assertSame('2', $this->nilaiFooter($this->get('/kontak-lokasi')->getContent(), 'Jumlah pengunjung'));
    }

    // ---- Pemeliharaan ----

    public function test_perintah_bersihkan_menghapus_baris_yang_melewati_masa_simpan(): void
    {
        config(['desa.statistik.masa_simpan_bulan' => 24]);
        StatistikKunjungan::query()->create(['tanggal' => now()->subMonths(25)->toDateString(), 'path' => '/', 'jumlah_tayang' => 5, 'jumlah_pengunjung' => 3]);
        StatistikKunjungan::query()->create(['tanggal' => now()->subMonths(23)->toDateString(), 'path' => '/', 'jumlah_tayang' => 5, 'jumlah_pengunjung' => 3]);

        $this->artisan('statistik:bersihkan', ['--hitung' => true])->assertSuccessful();
        $this->assertSame(2, StatistikKunjungan::count());

        $this->artisan('statistik:bersihkan')->assertSuccessful();
        $this->assertSame(1, StatistikKunjungan::count());
    }

    public function test_masa_simpan_nol_tidak_menghapus_apa_pun(): void
    {
        config(['desa.statistik.masa_simpan_bulan' => 0]);
        StatistikKunjungan::query()->create(['tanggal' => now()->subYears(5)->toDateString(), 'path' => '/', 'jumlah_tayang' => 1, 'jumlah_pengunjung' => 1]);

        $this->artisan('statistik:bersihkan')->assertSuccessful();

        $this->assertSame(1, StatistikKunjungan::count());
    }

    // ---- Kebijakan Privasi ----

    public function test_kebijakan_privasi_menjelaskan_penghitungan_kunjungan(): void
    {
        $this->get(route('kebijakan-privasi'))->assertOk()
            ->assertSee('Penghitungan jumlah kunjungan')
            ->assertSee('tanpa cookie dan tanpa pihak ketiga')
            ->assertSee('Alamat IP dan jenis peramban tidak disimpan di basis data')
            ->assertSee('selama 24 bulan, lalu dihapus otomatis')
            ->assertSee('Berlaku sejak 17 November 2026.')
            ->assertDontSee('tidak memakai layanan analitik');
    }

    public function test_kebijakan_privasi_mengikuti_config_statistik(): void
    {
        config(['desa.statistik.aktif' => false]);

        $this->get(route('kebijakan-privasi'))->assertOk()
            ->assertSee('Penghitungan jumlah kunjungan saat ini dimatikan')
            ->assertDontSee('tanpa cookie dan tanpa pihak ketiga');
    }
}
