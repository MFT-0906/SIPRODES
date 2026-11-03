<?php

namespace Tests\Feature;

use App\Models\Berita;
use App\Models\Jabatan;
use App\Models\Komoditas;
use App\Models\PerangkatDesa;
use App\Models\Umkm;
use App\Support\UnggahBerkas;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\Concerns\MasukSebagaiAdmin;
use Tests\TestCase;

class Fase8UnggahanTest extends TestCase
{
    use MasukSebagaiAdmin, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Storage::fake('local');
        $this->masukSebagaiAdmin();
    }

    private function berita(array $ganti = []): array
    {
        return $ganti + [
            'title' => 'Berita Gambar', 'excerpt' => 'Ringkasan.', 'isi_teks' => 'Isi.', 'tanggal' => '2026-10-29',
            'kategori' => 'Kegiatan', 'status' => 'draf', 'alt' => 'Deskripsi gambar',
        ];
    }

    /** JPEG asli dengan muatan PHP ditempel di akhir berkas (polyglot). */
    private function jpegPolyglot(): UploadedFile
    {
        $asli = UploadedFile::fake()->image('asli.jpg', 80, 60);
        $isi = file_get_contents($asli->getRealPath())."\n<?php system(\$_GET['c']); ?>";

        return UploadedFile::fake()->createWithContent('foto.jpg', $isi);
    }

    // ---- Layanan: pemrosesan gambar ----

    public function test_gambar_ditulis_ulang_sehingga_muatan_tersembunyi_dan_metadata_hilang(): void
    {
        $path = UnggahBerkas::simpanGambar($this->jpegPolyglot(), 'berita');

        $isi = Storage::disk('public')->get($path);
        $this->assertStringNotContainsString('<?php', $isi);
        $this->assertStringNotContainsString('system(', $isi);
        $this->assertStringNotContainsString('Exif', $isi);
        $this->assertNotFalse(getimagesizefromstring($isi));
        $this->assertSame(IMAGETYPE_JPEG, getimagesizefromstring($isi)[2]);
    }

    public function test_nama_acak_ekstensi_dari_isi_dan_folder_per_modul(): void
    {
        // Berkas PNG asli tetapi dinamai .php.jpg oleh pengguna: ekstensi ditentukan dari isi.
        $png = UploadedFile::fake()->image('serangan.php.jpg', 50, 50);
        $isi = file_get_contents($png->getRealPath());
        [, , $tipe] = getimagesizefromstring($isi);
        $path = UnggahBerkas::simpanGambar($png, 'komoditas');

        $this->assertMatchesRegularExpression('#^komoditas/[A-Za-z0-9]{40}\.(jpg|png|webp)$#', $path);
        $this->assertStringNotContainsString('serangan', $path);
        $this->assertStringNotContainsString('php', $path);
        $this->assertSame(IMAGETYPE_JPEG, $tipe);
        $this->assertStringEndsWith('.jpg', $path);
    }

    public function test_png_dan_webp_tetap_formatnya(): void
    {
        $png = UnggahBerkas::simpanGambar(UploadedFile::fake()->image('a.png', 40, 40), 'umkm');
        $this->assertStringEndsWith('.png', $png);
        $this->assertSame(IMAGETYPE_PNG, getimagesizefromstring(Storage::disk('public')->get($png))[2]);

        $webp = UnggahBerkas::simpanGambar(UploadedFile::fake()->image('b.webp', 40, 40), 'umkm');
        $this->assertStringEndsWith('.webp', $webp);
        $this->assertSame(IMAGETYPE_WEBP, getimagesizefromstring(Storage::disk('public')->get($webp))[2]);
    }

    public function test_gambar_besar_dikecilkan_ke_sisi_panjang_maksimum(): void
    {
        config(['desa.unggahan.gambar_sisi_panjang' => 500]);

        $path = UnggahBerkas::simpanGambar(UploadedFile::fake()->image('lebar.jpg', 1000, 400), 'berita');

        $this->assertSame([500, 200], array_slice(getimagesizefromstring(Storage::disk('public')->get($path)), 0, 2));

        $kecil = UnggahBerkas::simpanGambar(UploadedFile::fake()->image('kecil.jpg', 300, 200), 'berita');
        $this->assertSame([300, 200], array_slice(getimagesizefromstring(Storage::disk('public')->get($kecil)), 0, 2));
    }

    public function test_berkas_yang_bukan_gambar_ditolak_layanan(): void
    {
        $this->expectException(ValidationException::class);

        try {
            UnggahBerkas::simpanGambar(UploadedFile::fake()->createWithContent('x.jpg', "<?php echo 'x';"), 'berita', 'foto');
        } finally {
            $this->assertSame([], Storage::disk('public')->allFiles());
        }
    }

    // ---- Validasi lewat formulir ----

    public function test_polyglot_lolos_validasi_tetapi_yang_tersimpan_bersih(): void
    {
        $this->post(route('admin.berita.store'), $this->berita(['gambar' => $this->jpegPolyglot()]))->assertRedirect();

        $path = Berita::sole()->gambar_path;
        $this->assertStringNotContainsString('<?php', Storage::disk('public')->get($path));
    }

    public function test_skrip_berekstensi_gambar_ditolak_validasi(): void
    {
        foreach (['x.jpg', 'x.php', 'x.phtml', 'x.svg'] as $nama) {
            $this->post(route('admin.berita.store'), $this->berita(['gambar' => UploadedFile::fake()->createWithContent($nama, "<?php echo 'x';")]))
                ->assertSessionHasErrors('gambar');
        }
        $this->post(route('admin.berita.store'), $this->berita([
            'gambar' => UploadedFile::fake()->createWithContent('vektor.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'),
        ]))->assertSessionHasErrors(['gambar' => 'Ditolak: hanya JPG, PNG, atau WebP yang diizinkan. Pilih berkas lain.']);

        $this->assertSame(0, Berita::count());
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_batas_ukuran_dan_dimensi_dari_konfigurasi(): void
    {
        config(['desa.unggahan.gambar_maks_kb' => 100, 'desa.unggahan.gambar_maks_piksel' => 200]);

        $this->post(route('admin.berita.store'), $this->berita(['gambar' => UploadedFile::fake()->image('a.jpg')->size(150)]))
            ->assertSessionHasErrors(['gambar' => 'Ditolak: ukuran berkas melebihi 0,1 MB. Pilih berkas lain.']);
        $this->post(route('admin.berita.store'), $this->berita(['gambar' => UploadedFile::fake()->image('b.jpg', 250, 100)]))
            ->assertSessionHasErrors(['gambar' => 'Dimensi gambar maksimal 200 × 200 piksel.']);
        $this->post(route('admin.berita.store'), $this->berita(['gambar' => UploadedFile::fake()->image('c.jpg', 150, 100)]))
            ->assertSessionDoesntHaveErrors();
    }

    public function test_foto_perangkat_harus_persegi_di_semua_jalur(): void
    {
        Jabatan::factory()->create(['nama' => 'Kaur']);

        $this->post(route('admin.struktur.store'), ['nama' => 'A', 'jabatan' => 'Kaur', 'foto' => UploadedFile::fake()->image('p.jpg', 200, 100)])
            ->assertSessionHasErrors('foto');
        $this->post(route('admin.struktur.store'), ['nama' => 'A', 'jabatan' => 'Kaur', 'foto' => UploadedFile::fake()->image('p.jpg', 100, 100)])
            ->assertSessionDoesntHaveErrors();
    }

    // ---- Penghapusan berkas lama ----

    public function test_berkas_lama_terhapus_saat_diganti_atau_datanya_dihapus_di_setiap_modul(): void
    {
        Jabatan::factory()->create(['nama' => 'Kaur']);

        $this->post(route('admin.pertanian.store'), ['nama' => 'Jagung', 'status' => 'draf', 'foto' => UploadedFile::fake()->image('1.jpg'), 'alt' => 'a'])->assertRedirect();
        $this->post(route('admin.umkm.store'), ['nama' => 'U', 'produk' => 'P', 'foto' => UploadedFile::fake()->image('2.jpg'), 'alt' => 'a'])->assertRedirect();
        $this->post(route('admin.struktur.store'), ['nama' => 'A', 'jabatan' => 'Kaur', 'foto' => UploadedFile::fake()->image('3.jpg', 90, 90)])->assertRedirect();
        $this->post(route('admin.berita.store'), $this->berita(['gambar' => UploadedFile::fake()->image('4.jpg')]))->assertRedirect();
        $this->assertCount(4, Storage::disk('public')->allFiles());

        $this->put(route('admin.pertanian.update', Komoditas::sole()->id), ['nama' => 'Jagung', 'status' => 'draf', 'foto' => UploadedFile::fake()->image('5.jpg'), 'alt' => 'b'])->assertRedirect();
        $this->put(route('admin.umkm.update', Umkm::sole()->id), ['nama' => 'U', 'produk' => 'P', 'foto' => UploadedFile::fake()->image('6.jpg'), 'alt' => 'b'])->assertRedirect();
        $this->put(route('admin.struktur.update', PerangkatDesa::sole()->id), ['nama' => 'A', 'jabatan' => 'Kaur', 'foto' => UploadedFile::fake()->image('7.jpg', 90, 90)])->assertRedirect();
        $this->put(route('admin.berita.update', 'berita-gambar'), $this->berita(['gambar' => UploadedFile::fake()->image('8.jpg')]))->assertRedirect();
        $this->assertCount(4, Storage::disk('public')->allFiles(), 'Berkas lama harus terhapus saat diganti.');

        $this->delete(route('admin.pertanian.destroy', Komoditas::sole()->id));
        $this->delete(route('admin.umkm.destroy', Umkm::sole()->id));
        $this->delete(route('admin.struktur.destroy', PerangkatDesa::sole()->id));
        $this->delete(route('admin.berita.destroy', 'berita-gambar'));
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    // ---- Konfigurasi keamanan penyimpanan ----

    public function test_disk_privat_tidak_punya_rute_web_dan_htaccess_penyimpanan_publik_ada(): void
    {
        $this->assertFalse(config('filesystems.disks.local.serve'));
        $this->assertFileExists(storage_path('app/public/.htaccess'));
        $this->assertStringContainsString('Require all denied', file_get_contents(storage_path('app/public/.htaccess')));
        $this->assertStringContainsString('storage:link', file_get_contents(base_path('composer.json')));
    }

    public function test_pdf_disimpan_dengan_nama_acak_di_disk_privat(): void
    {
        $berkas = UploadedFile::fake()->createWithContent('Rahasia Desa.pdf', "%PDF-1.4\n%%EOF");

        $path = UnggahBerkas::simpanPdf($berkas, 'apbdes');

        $this->assertMatchesRegularExpression('#^apbdes/[A-Za-z0-9]{40}\.pdf$#', $path);
        Storage::disk('local')->assertExists($path);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_batas_mb_dan_format_untuk_uploader_frontend(): void
    {
        $this->assertSame(2.0, UnggahBerkas::batasMb('gambar'));
        $this->assertSame(10.0, UnggahBerkas::batasMb('pdf'));
        $this->assertSame('2', UnggahBerkas::formatMb(2.0));
        $this->assertSame('0,1', UnggahBerkas::formatMb(0.1));

        $this->assertMatchesRegularExpression('/maxMb.{1,12}:10[^0-9]/', $this->get(route('admin.apbdes', ['baru' => 1]))->assertOk()->getContent());
        $this->assertMatchesRegularExpression('/maxMb.{1,12}:2[^0-9]/', $this->get(route('admin.berita.create'))->assertOk()->getContent());
    }
}
