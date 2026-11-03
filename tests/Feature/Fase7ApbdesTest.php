<?php

namespace Tests\Feature;

use App\Enums\JenisAnggaran;
use App\Enums\Modul;
use App\Models\ApbdesDokumen;
use App\Models\ApbdesPeriode;
use App\Models\ApbdesRincian;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\MasukSebagaiAdmin;
use Tests\TestCase;

class Fase7ApbdesTest extends TestCase
{
    use MasukSebagaiAdmin, RefreshDatabase;

    private const PDF = "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\ntrailer\n<< /Root 1 0 R >>\n%%EOF\n";

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->masukSebagaiAdmin();
    }

    /** @return array<string, mixed> */
    private function data(array $ganti = []): array
    {
        return $ganti + [
            'tahun' => 2030, 'periode' => '1 Januari – 31 Desember 2030', 'status' => 'draf',
            'rincian' => [
                'pendapatan' => [['uraian' => 'Dana Desa', 'anggaran' => '1.000.000'], ['uraian' => 'Alokasi Dana Desa', 'anggaran' => '2.500.000']],
                'belanja' => [['uraian' => 'Pembangunan', 'anggaran' => '3.000.000']],
                'pembiayaan' => [],
            ],
        ];
    }

    private function pdf(string $nama = 'perdes.pdf', ?string $isi = null): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($nama, $isi ?? self::PDF);
    }

    // ---- Rincian dan total ----

    public function test_halaman_admin_menyediakan_uraian_bawaan_untuk_periode_baru(): void
    {
        $this->get(route('admin.apbdes', ['baru' => 1]))->assertOk()
            ->assertSee('Tambah Periode APBDes')->assertSee('Dana Desa')->assertSee('Pelaksanaan Pembangunan Desa')->assertSee('Pembiayaan netto')
            ->assertSee('Total dihitung otomatis');
        $this->get(route('admin.apbdes'))->assertOk()->assertSee('Belum ada periode APBDes');
    }

    public function test_total_dihitung_dari_rincian_dan_tampil_di_publik_hanya_setelah_terbit(): void
    {
        $this->post(route('admin.apbdes.store'), $this->data(['aksi' => 'draf']))->assertRedirect();
        $periode = ApbdesPeriode::sole();
        $this->assertSame(3_500_000, $periode->total(JenisAnggaran::Pendapatan));
        $this->assertSame(3_000_000, $periode->total(JenisAnggaran::Belanja));
        $this->assertNull($periode->diterbitkan_at);
        $this->assertFalse($periode->contoh);

        $this->get(route('apbdes'))->assertOk()->assertSee('Data APBDes belum dipublikasikan');

        $this->put(route('admin.apbdes.update', $periode->id), $this->data(['aksi' => 'terbit']))->assertRedirect(route('admin.apbdes', ['periode' => $periode->id]));
        $this->assertNotNull($periode->fresh()->diterbitkan_at);

        $this->get(route('apbdes'))->assertOk()
            ->assertSee('Rp 3.500.000')->assertSee('Rp 3.000.000')->assertSee('Dana Desa')->assertSee('Pembangunan')
            ->assertDontSee('Diagram belum tersedia')->assertDontSee('Data contoh');
    }

    public function test_rincian_diganti_seluruhnya_dan_baris_kosong_dibuang(): void
    {
        $this->post(route('admin.apbdes.store'), $this->data())->assertRedirect();
        $periode = ApbdesPeriode::sole();

        $this->put(route('admin.apbdes.update', $periode->id), $this->data([
            'rincian' => [
                'pendapatan' => [['uraian' => 'Hanya Satu', 'anggaran' => '500'], ['uraian' => '', 'anggaran' => '']],
                'belanja' => [['uraian' => 'Tanpa angka', 'anggaran' => '']],
                'pembiayaan' => [['uraian' => 'Pembiayaan netto', 'anggaran' => '10.000']],
            ],
        ]))->assertRedirect();

        $periode->refresh()->load('rincian');
        $this->assertSame(3, $periode->rincian->count());
        $this->assertSame(500, $periode->total(JenisAnggaran::Pendapatan));
        $this->assertNull($periode->total(JenisAnggaran::Belanja));
        $this->assertSame(10_000, $periode->total(JenisAnggaran::Pembiayaan));
        $this->assertSame(1, ApbdesRincian::where('uraian', 'Hanya Satu')->count());
    }

    public function test_validasi_angka_uraian_tahun_dan_tahun_unik(): void
    {
        $this->from(route('admin.apbdes'))->post(route('admin.apbdes.store'), $this->data([
            'rincian' => ['pendapatan' => [['uraian' => 'Dana', 'anggaran' => '12.500.000x'], ['uraian' => '', 'anggaran' => '100']]],
        ]))->assertSessionHasErrors(['rincian.pendapatan.0.anggaran' => 'Masukkan angka saja tanpa huruf.', 'rincian.pendapatan.1.uraian']);

        $this->post(route('admin.apbdes.store'), $this->data(['tahun' => 1999]))->assertSessionHasErrors('tahun');
        $this->post(route('admin.apbdes.store'), $this->data(['periode' => '', 'status' => 'rahasia']))->assertSessionHasErrors(['periode', 'status']);
        $this->assertSame(0, ApbdesPeriode::count());

        $this->post(route('admin.apbdes.store'), $this->data())->assertRedirect();
        $this->post(route('admin.apbdes.store'), $this->data())->assertSessionHasErrors(['tahun' => 'Periode APBDes untuk tahun ini sudah ada.']);
        $this->assertSame(1, ApbdesPeriode::count());

        // Mengubah periode sendiri dengan tahun yang sama tetap sah.
        $this->put(route('admin.apbdes.update', ApbdesPeriode::sole()->id), $this->data())->assertSessionDoesntHaveErrors();
    }

    public function test_periode_dapat_dihapus_beserta_rincian_dan_berkas(): void
    {
        $this->post(route('admin.apbdes.store'), $this->data(['dokumen' => $this->pdf(), 'judul_dokumen' => 'Perdes 2030']))->assertRedirect();
        $periode = ApbdesPeriode::sole();
        $path = ApbdesDokumen::sole()->path;
        Storage::disk('local')->assertExists($path);

        $this->delete(route('admin.apbdes.destroy', $periode->id))->assertRedirect(route('admin.apbdes'));

        $this->assertSame(0, ApbdesPeriode::count());
        $this->assertSame(0, ApbdesRincian::count());
        $this->assertSame(0, ApbdesDokumen::count());
        Storage::disk('local')->assertMissing($path);
        $this->delete(route('admin.apbdes.destroy', 999))->assertNotFound();
    }

    // ---- Dokumen PDF ----

    public function test_pdf_disimpan_di_disk_privat_dengan_nama_acak_dan_metadata(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $this->actingAs($admin)->post(route('admin.apbdes.store'), $this->data([
            'dokumen' => $this->pdf('Peraturan Desa.pdf'), 'judul_dokumen' => 'Peraturan Desa APBDes 2030', 'keterangan_periode' => 'Jan–Des 2030',
        ]))->assertRedirect();

        $dok = ApbdesDokumen::sole();
        $this->assertStringStartsWith('apbdes/', $dok->path);
        $this->assertNotSame('Peraturan Desa.pdf', basename($dok->path));
        Storage::disk('local')->assertExists($dok->path);
        Storage::disk('public')->assertMissing($dok->path);
        $this->assertSame(strlen(self::PDF), $dok->ukuran);
        $this->assertSame($admin->id, $dok->diunggah_oleh);
        $this->assertSame('Jan–Des 2030', $dok->keterangan_periode);
    }

    public function test_berkas_bukan_pdf_ditolak_walau_ekstensi_atau_tipe_dipalsukan(): void
    {
        $pesan = 'Ditolak: hanya PDF yang diizinkan. Pilih berkas lain.';
        $kirim = fn (UploadedFile $berkas) => $this->from(route('admin.apbdes'))->post(route('admin.apbdes.store'), $this->data(['dokumen' => $berkas, 'judul_dokumen' => 'X']));

        $kirim(UploadedFile::fake()->create('dokumen.exe', 10, 'application/x-msdownload'))->assertSessionHasErrors(['dokumen' => $pesan]);
        $kirim(UploadedFile::fake()->createWithContent('palsu.pdf', "<?php echo 'x';"))->assertSessionHasErrors('dokumen');
        $kirim(UploadedFile::fake()->createWithContent('gambar.pdf', "\x89PNG\r\n\x1a\n0000"))->assertSessionHasErrors('dokumen');
        $kirim(UploadedFile::fake()->create('besar.pdf', 11 * 1024, 'application/pdf'))->assertSessionHasErrors(['dokumen' => 'Ukuran PDF maksimal 10 MB.']);
        $kirim(UploadedFile::fake()->createWithContent('kosong.pdf', ''))->assertSessionHasErrors('dokumen');

        $this->assertSame(0, ApbdesPeriode::count());
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_judul_dokumen_wajib_bila_berkas_dipilih(): void
    {
        $this->from(route('admin.apbdes'))->post(route('admin.apbdes.store'), $this->data(['dokumen' => $this->pdf()]))
            ->assertSessionHasErrors('judul_dokumen');
        $this->assertSame(0, ApbdesPeriode::count());
    }

    public function test_hapus_dokumen_menghapus_berkas(): void
    {
        $this->post(route('admin.apbdes.store'), $this->data(['dokumen' => $this->pdf(), 'judul_dokumen' => 'Perdes']))->assertRedirect();
        $dok = ApbdesDokumen::sole();

        $this->delete(route('admin.apbdes.dokumen.destroy', $dok->id))->assertRedirect()->assertSessionHas('toast', 'Dokumen berhasil dihapus.');

        $this->assertSame(0, ApbdesDokumen::count());
        Storage::disk('local')->assertMissing($dok->path);
        $this->assertSame(1, ApbdesPeriode::count());
        $this->delete(route('admin.apbdes.dokumen.destroy', 999))->assertNotFound();
    }

    // ---- Unduhan publik ----

    private function dokumenDenganBerkas(bool $terbit, string $judul = 'Perdes APBDes 2030'): ApbdesDokumen
    {
        $periode = $terbit ? ApbdesPeriode::factory()->create(['tahun' => 2030]) : ApbdesPeriode::factory()->draf()->create(['tahun' => 2031]);
        Storage::disk('local')->put('apbdes/uji-'.$periode->tahun.'.pdf', self::PDF);

        return ApbdesDokumen::factory()->create(['periode_id' => $periode->id, 'judul' => $judul, 'path' => 'apbdes/uji-'.$periode->tahun.'.pdf', 'ukuran' => strlen(self::PDF)]);
    }

    public function test_pdf_periode_terbit_dapat_diunduh_publik_sebagai_lampiran(): void
    {
        $dok = $this->dokumenDenganBerkas(true);
        auth()->logout();

        $respons = $this->get(route('apbdes.unduh', $dok))->assertOk();
        $this->assertStringContainsString('attachment', $respons->headers->get('Content-Disposition'));
        $this->assertStringContainsString('perdes-apbdes-2030.pdf', $respons->headers->get('Content-Disposition'));
        $this->assertSame('application/pdf', $respons->headers->get('Content-Type'));
        $this->assertSame('nosniff', $respons->headers->get('X-Content-Type-Options'));
        $this->assertSame(self::PDF, $respons->streamedContent());
    }

    public function test_pdf_periode_draf_atau_tanpa_berkas_tidak_dapat_diunduh_publik(): void
    {
        $draf = $this->dokumenDenganBerkas(false);
        $kosong = ApbdesDokumen::factory()->create(['periode_id' => ApbdesPeriode::factory()->create(['tahun' => 2040])->id, 'path' => null]);
        $hilang = ApbdesDokumen::factory()->create(['periode_id' => $kosong->periode_id, 'path' => 'apbdes/tidak-ada.pdf']);
        auth()->logout();

        $this->get(route('apbdes.unduh', $draf))->assertNotFound();
        $this->get(route('apbdes.unduh', $kosong))->assertNotFound();
        $this->get(route('apbdes.unduh', $hilang))->assertNotFound();
        $this->get(route('apbdes.unduh', 9999))->assertNotFound();
        $this->get('/transparansi-apbdes/dokumen/abc')->assertNotFound();
    }

    public function test_berkas_privat_tidak_dapat_dibuka_lewat_url_penyimpanan_dan_tombol_unduh_aktif_di_publik(): void
    {
        $dok = $this->dokumenDenganBerkas(true);
        auth()->logout();

        $this->assertContains($this->get('/storage/'.$dok->path)->getStatusCode(), [403, 404]);
        $this->get(route('apbdes'))->assertOk()
            ->assertSee(route('apbdes.unduh', $dok), false)->assertSee('Unduh PDF')->assertDontSee('apbdes/uji-2030.pdf')->assertDontSee('Berkas belum diunggah');
    }

    public function test_admin_dapat_mengunduh_dokumen_periode_draf(): void
    {
        $dok = $this->dokumenDenganBerkas(false);

        $this->get(route('admin.apbdes.dokumen.unduh', $dok))->assertOk()->assertDownload('perdes-apbdes-2030.pdf');
    }

    // ---- Otorisasi ----

    public function test_modul_apbdes_hanya_untuk_yang_ditugaskan_dan_unduhan_admin_tertutup_untuk_tamu(): void
    {
        $dok = $this->dokumenDenganBerkas(false);

        $this->actingAs(User::factory()->denganModul([Modul::Berita])->create());
        $this->get(route('admin.apbdes'))->assertForbidden();
        $this->post(route('admin.apbdes.store'), $this->data())->assertForbidden();
        $this->get(route('admin.apbdes.dokumen.unduh', $dok))->assertForbidden();
        $this->delete(route('admin.apbdes.dokumen.destroy', $dok->id))->assertForbidden();

        auth()->logout();
        $this->get(route('admin.apbdes.dokumen.unduh', $dok))->assertRedirect(route('admin.login'));

        $this->actingAs(User::factory()->denganModul([Modul::Apbdes])->create());
        $this->post(route('admin.apbdes.store'), $this->data(['tahun' => 2035]))->assertRedirect();
    }
}
