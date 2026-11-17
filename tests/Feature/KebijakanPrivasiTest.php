<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Vite;
use Tests\TestCase;

/**
 * Halaman publik "Kebijakan Privasi": tampil, ditautkan dari footer, masuk sitemap, dan isinya
 * mengikuti perilaku sistem (tanpa NIK/IP pada aspirasi, tanpa pelacak).
 */
class KebijakanPrivasiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Jangan bergantung pada server dev Vite yang mungkin sedang berjalan (public/hot).
        Vite::useHotFile(storage_path('framework/testing/vite-hot-tidak-ada'));
    }

    public function test_halaman_tampil_dengan_judul_dan_bagian_utama(): void
    {
        $this->get(route('kebijakan-privasi'))
            ->assertOk()
            ->assertSee('Kebijakan Privasi')
            ->assertSee('Berlaku sejak 17 November 2026.')
            ->assertSee('Ruang Aspirasi')
            ->assertSee('Cloudflare Turnstile')
            ->assertSee('https://www.cloudflare.com/privacypolicy/', false)
            ->assertSee('Masuk dengan Google')
            ->assertSee('Cookie')
            ->assertSee('Undang-Undang Nomor 27 Tahun 2022');
    }

    public function test_url_halaman_adalah_kebijakan_privasi(): void
    {
        $this->assertSame(url('/kebijakan-privasi'), route('kebijakan-privasi'));
    }

    public function test_tautan_footer_ada_di_halaman_publik(): void
    {
        foreach (['home', 'kontak', 'kebijakan-privasi'] as $rute) {
            $html = $this->get(route($rute))->assertOk()->getContent();

            $this->assertMatchesRegularExpression(
                '#<footer\b.*<a href="'.preg_quote(route('kebijakan-privasi'), '#').'"[^>]*>\s*Kebijakan Privasi\s*</a>.*</footer>#s',
                $html,
                "Tautan Kebijakan Privasi tidak ada di footer halaman {$rute}",
            );
        }
    }

    public function test_url_ada_di_sitemap(): void
    {
        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee('<loc>'.route('kebijakan-privasi').'</loc>', false);
    }

    public function test_isi_mengikuti_masa_simpan_pada_konfigurasi(): void
    {
        config(['desa.aspirasi.masa_simpan_bulan' => 18, 'desa.log.masa_simpan_bulan' => 12]);

        $this->get(route('kebijakan-privasi'))
            ->assertSee('18 bulan setelah aspirasi dinyatakan selesai')
            ->assertSee('lebih tua dari 12 bulan');
    }

    public function test_kontak_hanya_tampil_bila_sudah_diverifikasi_dan_selain_itu_ada_tautan_kontak(): void
    {
        $this->get(route('kebijakan-privasi'))
            ->assertOk()
            ->assertSee('Kontak &amp; Lokasi', false)
            ->assertDontSee('mailto:');
    }

    public function test_view_halaman_tidak_memuat_img_tanpa_alt_dan_lazy(): void
    {
        $berkas = [
            resource_path('views/pages/public/kebijakan-privasi.blade.php'),
            resource_path('views/components/public/footer.blade.php'),
        ];

        foreach ($berkas as $path) {
            foreach (preg_split('/\R/', File::get($path)) as $baris) {
                if (str_contains($baris, '<img')) {
                    $this->assertStringContainsString('alt=', $baris);
                    $this->assertStringContainsString('loading="lazy"', $baris);
                }
            }
        }
    }
}
