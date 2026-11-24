<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\LogoDaerah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoDaerahTest extends TestCase
{
    use RefreshDatabase;

    private string $folder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->folder = sys_get_temp_dir().'/logo-daerah-'.uniqid();
        mkdir($this->folder.'/images', 0777, true);
        // Folder public sementara agar ada/tidaknya berkas logo bisa diatur; manifest Vite tidak ada di sana.
        $this->app->usePublicPath($this->folder);
        $this->withoutVite();
        config(['desa.logo.berkas' => 'images/logo-uji.png', 'desa.logo.alt' => 'Logo Kabupaten Lampung Selatan']);
    }

    protected function tearDown(): void
    {
        @unlink($this->folder.'/images/logo-uji.png');
        @unlink($this->folder.'/images/logo-uji.svg');
        @rmdir($this->folder.'/images');
        @rmdir($this->folder);

        parent::tearDown();
    }

    private function buatPng(int $lebar = 80, int $tinggi = 100): void
    {
        // PNG transparan minimal dibuat tanpa GD agar tes tidak bergantung pada ekstensi gambar.
        $ihdr = pack('NNCCCCC', $lebar, $tinggi, 8, 6, 0, 0, 0);
        $baris = str_repeat("\0".str_repeat("\0\0\0\0", $lebar), $tinggi);
        $potong = fn (string $jenis, string $data) => pack('N', strlen($data)).$jenis.$data.pack('N', crc32($jenis.$data));
        file_put_contents(
            $this->folder.'/images/logo-uji.png',
            "\x89PNG\r\n\x1a\n".$potong('IHDR', $ihdr).$potong('IDAT', gzcompress($baris)).$potong('IEND', ''),
        );
    }

    public function test_tanpa_berkas_logo_identitas_tetap_tulisan_dengan_garis_aksen(): void
    {
        $this->assertNull(LogoDaerah::data());

        $this->get(route('home'))->assertOk()
            ->assertDontSee('Logo Kabupaten Lampung Selatan')
            ->assertSee('h-10 w-1 shrink-0 rounded-full bg-accent', false);
    }

    public function test_logo_tampil_di_header_publik_dengan_alt_dan_ukuran_asli(): void
    {
        $this->buatPng(80, 100);

        $html = $this->get(route('home'))->assertOk()->getContent();

        $this->assertStringContainsString('alt="Logo Kabupaten Lampung Selatan"', $html);
        $this->assertStringContainsString('images/logo-uji.png?v=', $html);
        $this->assertStringContainsString('width="80" height="100"', $html);
        $this->assertStringNotContainsString('h-10 w-1 shrink-0 rounded-full bg-accent', $html);
    }

    public function test_logo_tampil_di_halaman_masuk_dan_dashboard(): void
    {
        $this->buatPng();

        $this->get(route('admin.login'))->assertOk()->assertSee('alt="Logo Kabupaten Lampung Selatan"', false);

        $admin = User::factory()->create();
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()
            ->assertSee('alt="Logo Kabupaten Lampung Selatan"', false);
    }

    public function test_svg_memakai_ukuran_viewbox(): void
    {
        config(['desa.logo.berkas' => 'images/logo-uji.svg']);
        file_put_contents($this->folder.'/images/logo-uji.svg', '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 150"></svg>');

        $data = LogoDaerah::data();

        $this->assertSame(120, $data['lebar']);
        $this->assertSame(150, $data['tinggi']);
    }

    public function test_nama_berkas_yang_tidak_aman_diabaikan(): void
    {
        $this->buatPng();

        foreach (['../.env', 'images/../../.env', 'images/logo uji.png', 'images/logo-uji.php', ''] as $berkas) {
            config(['desa.logo.berkas' => $berkas]);
            $this->assertNull(LogoDaerah::data(), "Berkas '{$berkas}' seharusnya ditolak");
        }
    }
}
