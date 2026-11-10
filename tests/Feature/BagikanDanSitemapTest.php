<?php

namespace Tests\Feature;

use App\Models\Berita;
use App\Support\KontenPublik;
use App\Support\VarianHero;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Pratinjau tautan (Open Graph), varian foto hero untuk HP, identitas tanpa logo, sitemap, dan robots.txt.
 */
class BagikanDanSitemapTest extends TestCase
{
    use RefreshDatabase;

    private ?string $folderUji = null;

    protected function tearDown(): void
    {
        if ($this->folderUji) {
            File::deleteDirectory($this->folderUji);
        }

        parent::tearDown();
    }

    /** Folder public sementara berisi foto hero palsu (isi bebas; hanya ukurannya yang dicocokkan). */
    private function publikSementara(bool $denganCatatan = true, ?int $ukuranCatatan = null): string
    {
        $this->folderUji = sys_get_temp_dir().'/siprodes-publik-'.uniqid();
        File::ensureDirectoryExists($this->folderUji.'/images');
        file_put_contents($this->folderUji.'/images/hero-desa.jpg', str_repeat('x', 1000));
        foreach (['hero-desa-800.webp', 'hero-desa-1600.webp', 'og-desa.jpg'] as $nama) {
            file_put_contents($this->folderUji.'/images/'.$nama, 'varian');
        }
        if ($denganCatatan) {
            file_put_contents($this->folderUji.'/images/hero-desa.json', json_encode([
                'sumber' => 'hero-desa.jpg',
                'ukuran_sumber' => $ukuranCatatan ?? 1000,
                'webp' => ['800' => 'hero-desa-800.webp', '1600' => 'hero-desa-1600.webp'],
                'og' => 'og-desa.jpg',
            ]));
        }
        $this->app->usePublicPath($this->folderUji);
        $this->withoutVite();

        return $this->folderUji;
    }

    public function test_beranda_punya_pratinjau_tautan_lengkap(): void
    {
        $this->publikSementara();

        $this->get(route('home'))->assertOk()
            ->assertSee('<meta property="og:type" content="website">', false)
            ->assertSee('<meta property="og:locale" content="id_ID">', false)
            ->assertSee('<meta property="og:url" content="'.route('home').'">', false)
            ->assertSee('og:description', false)
            ->assertSee('images/og-desa.jpg?v=', false)
            ->assertSee('<meta name="twitter:card" content="summary_large_image">', false)
            ->assertSee('<link rel="canonical" href="'.route('home').'">', false);
    }

    public function test_varian_dipakai_hanya_bila_cocok_dengan_foto_hero(): void
    {
        $this->publikSementara();
        $hero = KontenPublik::fotoHero();
        $this->assertStringContainsString('hero-desa-800.webp', $hero['srcset']);
        $this->assertStringContainsString(' 800w', $hero['srcset']);
        $this->assertStringContainsString(' 1600w', $hero['srcset']);
        $this->assertStringContainsString('og-desa.jpg', KontenPublik::gambarBagikan());

        $this->get(route('home'))->assertSee('srcset="', false);
        $this->get(route('profil'))->assertSee('srcset="', false);
    }

    public function test_foto_hero_diganti_tanpa_membuat_ulang_varian_kembali_ke_foto_utama(): void
    {
        // Catatan varian dibuat untuk foto berukuran lain: varian dianggap usang.
        $this->publikSementara(ukuranCatatan: 999);

        $hero = KontenPublik::fotoHero();
        $this->assertNull($hero['srcset']);
        $this->assertStringContainsString('hero-desa.jpg', KontenPublik::gambarBagikan());
        $this->get(route('home'))->assertDontSee('srcset="', false);
    }

    public function test_tanpa_catatan_varian_tetap_aman(): void
    {
        $this->publikSementara(denganCatatan: false);

        $this->assertNull(KontenPublik::fotoHero()['srcset']);
        $this->assertStringContainsString('hero-desa.jpg', KontenPublik::gambarBagikan());
    }

    public function test_catatan_varian_tidak_bisa_menunjuk_berkas_di_luar_folder_images(): void
    {
        $folder = $this->publikSementara();
        file_put_contents($folder.'/images/hero-desa.json', json_encode([
            'sumber' => 'hero-desa.jpg', 'ukuran_sumber' => 1000,
            'webp' => ['800' => '../../.env'], 'og' => '../index.php',
        ]));

        $this->assertNull(KontenPublik::fotoHero()['srcset']);
        $this->assertStringContainsString('hero-desa.jpg', KontenPublik::gambarBagikan());
    }

    public function test_berita_dengan_gambar_dibagikan_sebagai_artikel_dengan_gambarnya(): void
    {
        $berita = Berita::factory()->create(['gambar_path' => 'berita/contoh.jpg', 'gambar_alt' => 'Warga bergotong royong']);

        $this->get(route('berita.show', $berita->slug))->assertOk()
            ->assertSee('<meta property="og:type" content="article">', false)
            ->assertSee('<meta property="og:image" content="'.asset('storage/berita/contoh.jpg').'">', false)
            ->assertSee('<meta property="og:image:alt" content="Warga bergotong royong">', false);
    }

    public function test_header_tanpa_logo_menampilkan_identitas_tulisan(): void
    {
        $respons = $this->get(route('home'))->assertOk()->assertSee('Website resmi Desa Marga Agung')->assertDontSee('Pemerintah Desa');
        $header = str($respons->getContent())->after('<header')->before('</header>');

        $this->assertStringNotContainsString('bg-accent text-text"><svg', (string) $header);
        $this->assertStringContainsString('favicon.svg', $respons->getContent());
    }

    public function test_sitemap_berisi_halaman_publik_dan_berita_terbit_saja(): void
    {
        $terbit = Berita::factory()->create();
        $draf = Berita::factory()->draf()->create();

        $respons = $this->get('/sitemap.xml')->assertOk();
        $this->assertStringStartsWith('application/xml', $respons->headers->get('Content-Type'));

        $isi = $respons->getContent();
        foreach (['home', 'profil', 'apbdes', 'potensi', 'berita.index', 'kontak', 'aspirasi.create'] as $rute) {
            $this->assertStringContainsString('<loc>'.route($rute).'</loc>', $isi);
        }
        $this->assertStringContainsString('<loc>'.route('berita.show', $terbit->slug).'</loc>', $isi);
        $this->assertStringNotContainsString($draf->slug, $isi);
        $this->assertStringNotContainsString('/admin', $isi);
        $this->assertNotFalse(simplexml_load_string($isi));
    }

    public function test_robots_menunjuk_sitemap_sesuai_alamat_situs(): void
    {
        $this->get('/robots.txt')->assertOk()
            ->assertSee('User-agent: *', false)
            ->assertSee('Sitemap: '.route('sitemap'), false);
    }

    public function test_varian_hero_dibuat_dengan_gd(): void
    {
        if (! function_exists('imagewebp') || ! function_exists('imagejpeg')) {
            $this->markTestSkipped('GD tanpa dukungan WebP/JPEG di lingkungan ini.');
        }

        $folder = $this->publikSementara(denganCatatan: false);
        $gambar = imagecreatetruecolor(2000, 1125);
        imagejpeg($gambar, $folder.'/images/hero-desa.jpg');

        $this->artisan('desa:foto-hero')->assertSuccessful();

        $this->assertSame([1200, 630], array_slice(getimagesize($folder.'/images/og-desa.jpg'), 0, 2));
        $this->assertSame(800, getimagesize($folder.'/images/hero-desa-800.webp')[0]);
        $this->assertSame(1600, getimagesize($folder.'/images/hero-desa-1600.webp')[0]);
        $this->assertFileDoesNotExist($folder.'/images/hero-desa-2400.webp'); // Foto tidak diperbesar.
        $this->assertNotNull(KontenPublik::fotoHero()['srcset']);
        $this->assertSame(VarianHero::LEBAR_WEBP, [800, 1600, 2400]);
    }
}
