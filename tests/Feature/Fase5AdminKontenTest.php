<?php

namespace Tests\Feature;

use App\Enums\Modul;
use App\Enums\StatusTerbit;
use App\Models\Agenda;
use App\Models\Aspirasi;
use App\Models\Berita;
use App\Models\KelompokTani;
use App\Models\Komoditas;
use App\Models\Umkm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\MasukSebagaiAdmin;
use Tests\TestCase;

class Fase5AdminKontenTest extends TestCase
{
    use MasukSebagaiAdmin, RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->admin = $this->masukSebagaiAdmin();
    }

    /** @return array<string, mixed> */
    private function dataBerita(array $ganti = []): array
    {
        return $ganti + [
            'title' => 'Berita Uji Alur', 'excerpt' => 'Ringkasan berita uji.', 'isi_teks' => "Paragraf pertama.\n\n## Subjudul",
            'tanggal' => '2026-10-29', 'kategori' => 'Kegiatan', 'status' => 'terbit',
        ];
    }

    // ---- Ringkasan ----

    public function test_ringkasan_dihitung_dari_database(): void
    {
        Berita::factory()->count(3)->create();
        Berita::factory()->draf()->count(2)->create();
        Umkm::factory()->count(4)->create();
        Umkm::factory()->draf()->create();
        Komoditas::factory()->count(2)->create();
        KelompokTani::factory()->count(3)->create();
        Aspirasi::factory()->count(2)->create();
        Aspirasi::factory()->selesai()->create();

        $this->get(route('admin.dashboard'))->assertOk()
            ->assertViewHas('terbit', 3)->assertViewHas('draf', 2)->assertViewHas('umkm', 4)
            ->assertViewHas('komoditas', 2)->assertViewHas('kelompokTani', 3)->assertViewHas('aspirasiBaru', 2)
            ->assertSee('Konten Draf Terakhir');
    }

    public function test_ringkasan_kosong_tidak_menampilkan_angka_karangan(): void
    {
        $this->get(route('admin.dashboard'))->assertOk()
            ->assertViewHas('terbit', 0)->assertViewHas('aspirasiBaru', 0)
            ->assertSee('Belum ada aspirasi masuk')->assertSee('Tidak ada konten draf');
    }

    public function test_draf_terakhir_memuat_berita_dan_agenda_draf(): void
    {
        Berita::factory()->draf()->create(['judul' => 'Draf Berita Terakhir']);
        Agenda::factory()->draf()->create(['judul' => 'Draf Agenda Terakhir']);
        Berita::factory()->create(['judul' => 'Berita Sudah Tayang']);

        $this->get(route('admin.dashboard'))->assertOk()
            ->assertSee('Draf Berita Terakhir')->assertSee('Draf Agenda Terakhir')->assertDontSee('Berita Sudah Tayang');
    }

    public function test_ringkasan_staf_tidak_memuat_data_modul_yang_tidak_ditugaskan(): void
    {
        Aspirasi::factory()->create(['nama' => 'Warga Rahasia', 'pesan' => 'Isi pesan rahasia warga']);
        Berita::factory()->draf()->create(['judul' => 'Draf Internal']);
        Umkm::factory()->count(2)->create();
        $staf = User::factory()->denganModul([Modul::Berita])->create();

        $this->actingAs($staf)->get(route('admin.dashboard'))->assertOk()
            ->assertViewHas('aspirasiBaru', 0)->assertViewHas('umkm', 0)->assertViewHas('aspirasiTerbaru', [])
            ->assertDontSee('Warga Rahasia')->assertDontSee('Isi pesan rahasia')
            ->assertSee('Draf Internal');
    }

    // ---- Berita ----

    public function test_daftar_berita_admin_menampilkan_draf_dan_terbit_dengan_filter(): void
    {
        Berita::factory()->create(['judul' => 'Berita Terbit Satu', 'kategori' => 'Kegiatan']);
        Berita::factory()->draf()->create(['judul' => 'Berita Draf Satu', 'kategori' => 'Pertanian']);

        $this->get(route('admin.berita.index'))->assertOk()
            ->assertSee('Berita Terbit Satu')->assertSee('Berita Draf Satu')->assertSee('Hapus berita ini?', false);
        $this->get(route('admin.berita.index', ['status' => 'draf']))->assertSee('Berita Draf Satu')->assertDontSee('Berita Terbit Satu');
        $this->get(route('admin.berita.index', ['kategori' => 'Kegiatan']))->assertSee('Berita Terbit Satu')->assertDontSee('Berita Draf Satu');
        $this->get(route('admin.berita.index', ['q' => 'Draf Satu']))->assertSee('Berita Draf Satu')->assertDontSee('Berita Terbit Satu');
        $this->get(route('admin.berita.index', ['q' => 'tidakadakatakunciini']))->assertSee('Tidak ada berita yang cocok');
        $this->get(route('admin.berita.index', ['q' => "%' OR 1=1 --"]))->assertOk()->assertDontSee('Berita Terbit Satu');
    }

    public function test_daftar_berita_dipaginasi_dan_keadaan_kosong(): void
    {
        $this->get(route('admin.berita.index'))->assertOk()->assertSee('Belum ada berita');

        Berita::factory()->count(7)->create();
        $this->get(route('admin.berita.index'))->assertOk()->assertSee('dari 7 berita');
        $this->get(route('admin.berita.index', ['page' => 2]))->assertOk()->assertSee('6–7 dari 7');
    }

    public function test_tambah_berita_tersimpan_dengan_slug_penulis_dan_status_dari_tombol(): void
    {
        $this->post(route('admin.berita.store'), $this->dataBerita(['status' => 'terbit', 'aksi' => 'draf']))
            ->assertRedirect(route('admin.berita.index'))->assertSessionHas('toast', 'Perubahan berhasil disimpan.');

        $berita = Berita::sole();
        $this->assertSame('berita-uji-alur', $berita->slug);
        $this->assertSame(StatusTerbit::Draf, $berita->status);
        $this->assertNull($berita->diterbitkan_at);
        $this->assertSame($this->admin->id, $berita->penulis_id);
        $this->assertSame('2026-10-29', $berita->tanggal->toDateString());
    }

    public function test_slug_unik_otomatis_untuk_judul_yang_sama(): void
    {
        foreach (range(1, 3) as $_) {
            $this->post(route('admin.berita.store'), $this->dataBerita())->assertRedirect();
        }

        $this->assertEqualsCanonicalizing(['berita-uji-alur', 'berita-uji-alur-2', 'berita-uji-alur-3'], Berita::pluck('slug')->all());
    }

    public function test_terbit_langsung_tampil_di_publik_dan_draf_tidak(): void
    {
        $this->post(route('admin.berita.store'), $this->dataBerita(['title' => 'Berita Tayang Baru', 'aksi' => 'terbit']))->assertRedirect();
        $this->post(route('admin.berita.store'), $this->dataBerita(['title' => 'Berita Masih Draf', 'aksi' => 'draf']))->assertRedirect();

        $this->assertNotNull(Berita::where('slug', 'berita-tayang-baru')->value('diterbitkan_at'));
        $this->get(route('berita.index'))->assertSee('Berita Tayang Baru')->assertDontSee('Berita Masih Draf');
        $this->get(route('home'))->assertSee('Berita Tayang Baru')->assertDontSee('Berita Masih Draf');
        $this->get(route('berita.show', 'berita-tayang-baru'))->assertOk()->assertSee('Subjudul');
        $this->get(route('berita.show', 'berita-masih-draf'))->assertNotFound();
    }

    public function test_ubah_berita_menjaga_slug_dan_menarik_dari_publik_saat_jadi_draf(): void
    {
        $berita = Berita::factory()->create(['judul' => 'Judul Awal', 'slug' => 'judul-awal']);

        $this->get(route('admin.berita.edit', 'judul-awal'))->assertOk()->assertSee('Ubah Berita')->assertSee('Judul Awal');

        $this->put(route('admin.berita.update', 'judul-awal'), $this->dataBerita(['title' => 'Judul Baru', 'aksi' => 'draf']))
            ->assertRedirect(route('admin.berita.index'));

        $berita->refresh();
        $this->assertSame('judul-awal', $berita->slug);
        $this->assertSame('Judul Baru', $berita->judul);
        $this->assertSame(StatusTerbit::Draf, $berita->status);
        $this->get(route('berita.show', 'judul-awal'))->assertNotFound();
        $this->get(route('berita.index'))->assertDontSee('Judul Baru');
    }

    public function test_tanggal_diterbitkan_tidak_berubah_saat_disimpan_ulang_sebagai_terbit(): void
    {
        $berita = Berita::factory()->create(['slug' => 'tetap', 'diterbitkan_at' => '2026-01-02 03:04:05']);

        $this->put(route('admin.berita.update', 'tetap'), $this->dataBerita(['aksi' => 'terbit']))->assertRedirect();

        $this->assertSame('2026-01-02 03:04:05', $berita->fresh()->diterbitkan_at->format('Y-m-d H:i:s'));
    }

    public function test_hapus_berita_menghapus_baris_dan_gambarnya(): void
    {
        $this->post(route('admin.berita.store'), $this->dataBerita([
            'gambar' => UploadedFile::fake()->image('foto.jpg', 600, 400), 'alt' => 'Warga bergotong royong',
        ]))->assertRedirect();
        $path = Berita::sole()->gambar_path;
        Storage::disk('public')->assertExists($path);

        $this->delete(route('admin.berita.destroy', 'berita-uji-alur'))
            ->assertRedirect(route('admin.berita.index'))->assertSessionHas('toast', 'Berita berhasil dihapus.');

        $this->assertSame(0, Berita::count());
        Storage::disk('public')->assertMissing($path);
    }

    public function test_berita_tidak_ada_mengembalikan_404(): void
    {
        $this->delete(route('admin.berita.destroy', 'tidak-ada'))->assertNotFound();
        $this->get(route('admin.berita.edit', 'tidak-ada'))->assertNotFound();
        $this->put(route('admin.berita.update', 'tidak-ada'), $this->dataBerita())->assertNotFound();
    }

    public function test_form_berita_memvalidasi_isian_wajib(): void
    {
        $this->from(route('admin.berita.create'))->post(route('admin.berita.store'), [])
            ->assertRedirect(route('admin.berita.create'))
            ->assertSessionHasErrors(['title', 'excerpt', 'isi_teks', 'tanggal', 'kategori', 'status']);

        $this->post(route('admin.berita.store'), $this->dataBerita(['status' => 'rahasia', 'tanggal' => 'bukan-tanggal']))
            ->assertSessionHasErrors(['status', 'tanggal']);
        $this->assertSame(0, Berita::count());
    }

    // ---- Gambar utama ----

    public function test_gambar_tersimpan_dan_alt_wajib(): void
    {
        $this->post(route('admin.berita.store'), $this->dataBerita(['gambar' => UploadedFile::fake()->image('foto.jpg')]))
            ->assertSessionHasErrors('alt');
        $this->assertSame(0, Berita::count());

        $this->post(route('admin.berita.store'), $this->dataBerita(['gambar' => UploadedFile::fake()->image('foto.png'), 'alt' => 'Deskripsi gambar']))
            ->assertRedirect();

        $berita = Berita::sole();
        Storage::disk('public')->assertExists($berita->gambar_path);
        $this->assertStringStartsWith('berita/', $berita->gambar_path);
        $this->assertNotSame('foto.png', basename($berita->gambar_path));
        $this->assertSame('Deskripsi gambar', $berita->gambar_alt);
        $this->get(route('berita.show', $berita->slug))->assertOk()->assertSee('alt="Deskripsi gambar"', false);
    }

    public function test_gambar_ditolak_bila_terlalu_besar_salah_tipe_atau_dimensi_berlebih(): void
    {
        $this->post(route('admin.berita.store'), $this->dataBerita(['gambar' => UploadedFile::fake()->image('besar.jpg')->size(3000), 'alt' => 'x']))
            ->assertSessionHasErrors(['gambar' => 'Ukuran gambar maksimal 2 MB.']);
        $this->post(route('admin.berita.store'), $this->dataBerita(['gambar' => UploadedFile::fake()->create('dokumen.jpg', 10, 'application/pdf'), 'alt' => 'x']))
            ->assertSessionHasErrors(['gambar' => 'Gambar harus berformat JPG, PNG, atau WebP.']);
        $this->post(route('admin.berita.store'), $this->dataBerita(['gambar' => UploadedFile::fake()->image('lebar.jpg', 4100, 100), 'alt' => 'x']))
            ->assertSessionHasErrors('gambar');

        $this->assertSame(0, Berita::count());
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_mengganti_dan_menghapus_gambar_membersihkan_berkas_lama(): void
    {
        $this->post(route('admin.berita.store'), $this->dataBerita(['gambar' => UploadedFile::fake()->image('a.jpg'), 'alt' => 'Pertama']))->assertRedirect();
        $lama = Berita::sole()->gambar_path;

        $this->put(route('admin.berita.update', 'berita-uji-alur'), $this->dataBerita(['gambar' => UploadedFile::fake()->image('b.jpg'), 'alt' => 'Kedua']))->assertRedirect();
        $baru = Berita::sole()->gambar_path;
        $this->assertNotSame($lama, $baru);
        Storage::disk('public')->assertMissing($lama);
        Storage::disk('public')->assertExists($baru);

        // Tanpa unggahan baru, gambar dipertahankan dan alt bisa diperbarui.
        $this->put(route('admin.berita.update', 'berita-uji-alur'), $this->dataBerita(['alt' => 'Alt diperbarui']))->assertRedirect();
        $this->assertSame($baru, Berita::sole()->gambar_path);
        $this->assertSame('Alt diperbarui', Berita::sole()->gambar_alt);

        $this->put(route('admin.berita.update', 'berita-uji-alur'), $this->dataBerita(['hapus_gambar' => '1']))->assertRedirect();
        $this->assertNull(Berita::sole()->gambar_path);
        Storage::disk('public')->assertMissing($baru);
    }

    // ---- Isi berita: render aman ----

    public function test_isi_dirender_aman_script_dan_tautan_berbahaya_tidak_muncul(): void
    {
        $this->post(route('admin.berita.store'), $this->dataBerita([
            'isi_teks' => "<script>alert('xss')</script>\n\n<img src=x onerror=alert(1)>\n\n[klik](javascript:alert(1)) dan [aman](https://contoh.test)\n\n**tebal** dan _miring_\n\n## Subjudul Aman\n\n> Kutipan\n\n- butir satu\n- butir dua",
        ]))->assertRedirect();

        $html = $this->get(route('berita.show', 'berita-uji-alur'))->assertOk()->getContent();

        $this->assertStringNotContainsString('<script>alert', $html);
        $this->assertStringNotContainsString('onerror', $html);
        $this->assertStringNotContainsString('javascript:', $html);
        $this->assertStringContainsString('href="https://contoh.test"', $html);
        $this->assertStringContainsString('<strong>tebal</strong>', $html);
        $this->assertStringContainsString('<em>miring</em>', $html);
        $this->assertStringContainsString('<h2>Subjudul Aman</h2>', $html);
        $this->assertStringContainsString('<blockquote>', $html);
        $this->assertStringContainsString('<li>butir dua</li>', $html);
    }

    public function test_judul_dan_ringkasan_di_daftar_admin_dan_publik_di_escape(): void
    {
        $payload = '<script>alert(1)</script>';
        $this->post(route('admin.berita.store'), $this->dataBerita(['title' => $payload, 'excerpt' => $payload]))->assertRedirect();

        foreach ([route('admin.berita.index'), route('berita.index'), route('home')] as $url) {
            $this->get($url)->assertOk()->assertDontSee($payload, false);
        }
    }

    // ---- Agenda ----

    /** @return array<string, mixed> */
    private function dataAgenda(array $ganti = []): array
    {
        return $ganti + ['title' => 'Agenda Uji', 'tanggal' => now()->addDays(5)->toDateString(), 'waktu' => '10:30', 'lokasi' => 'Balai Desa', 'status' => 'terbit', 'deskripsi' => 'Keterangan.'];
    }

    public function test_agenda_tambah_ubah_hapus_tersimpan_di_database(): void
    {
        $this->post(route('admin.agenda.store'), $this->dataAgenda(['aksi' => 'terbit']))
            ->assertRedirect(route('admin.agenda.index', ['waktu' => 'semua']));

        $agenda = Agenda::sole();
        $this->assertSame('Agenda Uji', $agenda->judul);
        $this->assertSame(StatusTerbit::Terbit, $agenda->status);
        $this->assertSame('10:30', substr((string) $agenda->waktu, 0, 5));
        $this->get(route('admin.agenda.index', ['waktu' => 'semua']))->assertSee('Agenda Uji')->assertSee('10.30')->assertSee('Balai Desa');

        $this->get(route('admin.agenda.index', ['ubah' => $agenda->id, 'waktu' => 'semua']))->assertSee('Ubah Agenda');

        $this->put(route('admin.agenda.update', $agenda->id), $this->dataAgenda(['title' => 'Agenda Diubah', 'waktu' => '', 'lokasi' => '', 'aksi' => 'draf']))->assertRedirect();
        $agenda->refresh();
        $this->assertSame('Agenda Diubah', $agenda->judul);
        $this->assertNull($agenda->waktu);
        $this->assertNull($agenda->lokasi);
        $this->assertSame(StatusTerbit::Draf, $agenda->status);

        $this->delete(route('admin.agenda.destroy', $agenda->id))->assertRedirect()->assertSessionHas('toast', 'Agenda berhasil dihapus.');
        $this->assertSame(0, Agenda::count());
    }

    public function test_filter_waktu_status_dan_pencarian_agenda(): void
    {
        Agenda::factory()->create(['judul' => 'Rapat Mendatang']);
        Agenda::factory()->lewat()->create(['judul' => 'Rapat Lewat']);
        Agenda::factory()->draf()->create(['judul' => 'Rapat Draf']);

        $this->get(route('admin.agenda.index'))->assertSee('Rapat Mendatang')->assertSee('Rapat Draf')->assertDontSee('Rapat Lewat');
        $this->get(route('admin.agenda.index', ['waktu' => 'lewat']))->assertSee('Rapat Lewat')->assertDontSee('Rapat Mendatang');
        $this->get(route('admin.agenda.index', ['waktu' => 'semua', 'status' => 'draf']))->assertSee('Rapat Draf')->assertDontSee('Rapat Lewat');
        $this->get(route('admin.agenda.index', ['waktu' => 'semua', 'q' => 'Rapat Lewat']))->assertSee('dari 1 agenda');
    }

    public function test_form_agenda_memvalidasi_isian_dan_id_bukan_angka_404(): void
    {
        $this->from(route('admin.agenda.index'))->post(route('admin.agenda.store'), ['waktu' => 'bukan-jam'])
            ->assertSessionHasErrors(['title', 'tanggal', 'waktu', 'status']);

        $this->put(route('admin.agenda.update', 99999), $this->dataAgenda())->assertNotFound();
        $this->delete('/admin/agenda/abc')->assertNotFound();
    }

    public function test_agenda_draf_tidak_tampil_di_publik(): void
    {
        Agenda::factory()->draf()->create(['judul' => 'Agenda Draf Rahasia']);
        Agenda::factory()->create(['judul' => 'Agenda Tayang']);

        $this->get(route('berita.index'))->assertOk()->assertSee('Agenda Tayang')->assertDontSee('Agenda Draf Rahasia');
        $this->get(route('home'))->assertOk()->assertSee('Agenda Tayang')->assertDontSee('Agenda Draf Rahasia');
    }

    // ---- Otorisasi ----

    public function test_staf_berita_dapat_mengelola_dan_staf_lain_ditolak(): void
    {
        $this->actingAs(User::factory()->denganModul([Modul::Berita])->create());
        $this->post(route('admin.berita.store'), $this->dataBerita())->assertRedirect();
        $this->post(route('admin.agenda.store'), $this->dataAgenda())->assertRedirect();

        $this->actingAs(User::factory()->denganModul([Modul::Potensi])->create());
        $this->post(route('admin.berita.store'), $this->dataBerita(['title' => 'Tidak Boleh']))->assertForbidden();
        $this->delete(route('admin.berita.destroy', 'berita-uji-alur'))->assertForbidden();
        $this->post(route('admin.agenda.store'), $this->dataAgenda())->assertForbidden();

        $this->assertSame(1, Berita::count());
        $this->assertSame(1, Agenda::count());
    }
}
