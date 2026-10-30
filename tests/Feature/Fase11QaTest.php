<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class Fase11QaTest extends TestCase
{
    public function test_setiap_img_di_template_punya_alt_dan_lazy_loading(): void
    {
        $berkas = collect(File::allFiles(resource_path('views')))
            ->filter(fn ($f) => str_ends_with($f->getFilename(), '.blade.php'));

        $img = $berkas->flatMap(fn ($f) => collect(preg_split('/\R/', $f->getContents()))
            ->filter(fn (string $baris) => str_contains($baris, '<img') && ! str_contains($baris, '{{--'))
            ->map(fn (string $baris) => [$f->getFilename(), $baris]));

        $this->assertNotEmpty($img);
        foreach ($img as [$nama, $baris]) {
            $this->assertStringContainsString('alt=', $baris, "<img tanpa alt di {$nama}");
            $this->assertStringContainsString('loading="lazy"', $baris, "<img tanpa loading=lazy di {$nama}");
        }
    }

    public function test_halaman_publik_tidak_memuat_konten_draf(): void
    {
        foreach (['home', 'berita.index', 'potensi', 'kontak'] as $nama) {
            $this->get(route($nama))->assertOk()
                ->assertDontSee('Draf berita yang belum diterbitkan')
                ->assertDontSee('Agenda draf contoh');
        }
        $this->get('/berita/draf-belum-terbit')->assertNotFound();
    }

    public function test_halaman_admin_dan_auth_tidak_boleh_diindeks(): void
    {
        foreach ([route('admin.login'), route('admin.dashboard'), route('admin.berita.index')] as $url) {
            $this->get($url)->assertOk()->assertSee('noindex', false);
        }
    }

    public function test_setiap_halaman_memiliki_satu_h1(): void
    {
        foreach (['/', '/profil-desa', '/transparansi-apbdes', '/potensi-desa', '/berita', '/kontak-lokasi', '/ruang-aspirasi', '/admin', '/admin/aspirasi', '/admin/berita', '/admin/agenda', '/admin/profil?tab=data', '/admin/apbdes', '/admin/struktur-perangkat', '/admin/potensi-pertanian', '/admin/kelompok-tani', '/admin/umkm', '/admin/masuk', '/admin/lupa-kata-sandi'] as $path) {
            $this->assertSame(1, substr_count($this->get($path)->assertOk()->getContent(), '<h1'), "Jumlah <h1> di {$path}");
        }
    }
    public function test_isian_admin_ditampilkan_sebagai_teks_bukan_skrip(): void
    {
        $payload = '<script>alert(1)</script>';

        $this->post(route('admin.berita.store'), [
            'title' => $payload, 'excerpt' => $payload, 'isi_teks' => $payload, 'tanggal' => '2026-10-30',
            'kategori' => 'Kegiatan', 'status' => 'draf',
        ])->assertRedirect();

        $this->get(route('admin.berita.index'))->assertOk()
            ->assertDontSee($payload, false)
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
    }
}
