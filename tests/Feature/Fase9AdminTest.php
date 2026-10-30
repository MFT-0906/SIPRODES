<?php

namespace Tests\Feature;

use Tests\TestCase;

class Fase9AdminTest extends TestCase
{
    public function test_ringkasan_menampilkan_statistik_aspirasi_dan_draf(): void
    {
        $this->get(route('admin.dashboard'))->assertOk()
            ->assertSee('Selamat datang, Nama Admin')
            ->assertSee('Berita terbit')
            ->assertSee('Aspirasi belum diproses')
            ->assertSee('Aspirasi Terbaru')
            ->assertSee('Pengirim A')
            ->assertSee('Konten Draf Terakhir')
            ->assertSee('Draf berita yang belum diterbitkan')
            ->assertSee('Agenda draf contoh');
    }

    public function test_daftar_berita_admin_menampilkan_draf_dan_terbit(): void
    {
        $this->get(route('admin.berita.index'))->assertOk()
            ->assertSee('Gotong royong, lingkungan bersih dan warga makin akrab')
            ->assertSee('Draf berita yang belum diterbitkan')
            ->assertSee('Hapus berita ini?', false);
    }

    public function test_filter_dan_pencarian_berita_admin(): void
    {
        $this->get(route('admin.berita.index', ['status' => 'draf']))->assertOk()
            ->assertSee('Draf berita yang belum diterbitkan')
            ->assertDontSee('Mengolah jagung menjadi peluang usaha bersama');

        $this->get(route('admin.berita.index', ['q' => 'tidakadakatakunciini']))->assertOk()
            ->assertSee('Tidak ada berita yang cocok');
    }

    public function test_tabel_berita_kosong_hanya_di_lokal(): void
    {
        $this->get(route('admin.berita.index', ['demo' => 'kosong']))->assertOk()->assertDontSee('Belum ada berita');

        $this->app->detectEnvironment(fn () => 'local');
        $this->get(route('admin.berita.index', ['demo' => 'kosong']))->assertOk()
            ->assertSee('Belum ada berita')->assertSee('Mulai dengan menambahkan berita pertama');
    }

    public function test_tambah_berita_muncul_di_tabel_lalu_dapat_dihapus(): void
    {
        $this->post(route('admin.berita.store'), [
            'title' => 'Berita uji alur tambah',
            'excerpt' => 'Ringkasan berita uji.',
            'isi_teks' => "Paragraf pertama.\n\n## Subjudul",
            'tanggal' => '2026-10-29',
            'kategori' => 'Kegiatan',
            'status' => 'terbit',
            'aksi' => 'draf',
        ])->assertRedirect(route('admin.berita.index'))->assertSessionHas('toast', 'Perubahan berhasil disimpan.');

        // Tombol yang ditekan (aksi) menentukan status akhir.
        $this->get(route('admin.berita.index', ['status' => 'draf']))->assertOk()->assertSee('Berita uji alur tambah');

        $this->delete(route('admin.berita.destroy', 'berita-uji-alur-tambah'))
            ->assertRedirect(route('admin.berita.index'))
            ->assertSessionHas('toast', 'Berita berhasil dihapus.');

        $this->get(route('admin.berita.index'))->assertDontSee('Berita uji alur tambah');
    }

    public function test_ubah_berita_memperbarui_data(): void
    {
        $this->get(route('admin.berita.edit', 'draf-belum-terbit'))->assertOk()
            ->assertSee('Ubah Berita')->assertSee('Draf berita yang belum diterbitkan');

        $this->put(route('admin.berita.update', 'draf-belum-terbit'), [
            'title' => 'Judul sudah diubah',
            'excerpt' => 'Ringkasan baru.',
            'isi_teks' => 'Isi baru.',
            'tanggal' => '2026-10-29',
            'kategori' => 'Pengumuman',
            'status' => 'terbit',
        ])->assertRedirect(route('admin.berita.index'));

        $this->get(route('admin.berita.index'))->assertSee('Judul sudah diubah');
    }

    public function test_form_berita_memvalidasi_isian_wajib_dan_alt_untuk_gambar(): void
    {
        $this->from(route('admin.berita.create'))->post(route('admin.berita.store'), [])
            ->assertRedirect(route('admin.berita.create'))
            ->assertSessionHasErrors(['title', 'excerpt', 'isi_teks', 'tanggal', 'kategori', 'status']);

        $this->post(route('admin.berita.store'), [
            'title' => 'Judul', 'excerpt' => 'Ringkasan', 'isi_teks' => 'Isi', 'tanggal' => '2026-10-29',
            'kategori' => 'Kegiatan', 'status' => 'draf',
            'gambar' => \Illuminate\Http\UploadedFile::fake()->image('foto.jpg'),
        ])->assertSessionHasErrors('alt');
    }

    public function test_hapus_berita_yang_tidak_ada_mengembalikan_404(): void
    {
        $this->delete(route('admin.berita.destroy', 'tidak-ada'))->assertNotFound();
        $this->get(route('admin.berita.edit', 'tidak-ada'))->assertNotFound();
    }

    public function test_agenda_admin_menampilkan_tabel_dan_formulir(): void
    {
        $this->get(route('admin.agenda.index', ['waktu' => 'semua']))->assertOk()
            ->assertSee('Musyawarah Desa')
            ->assertSee('Agenda draf contoh')
            ->assertSee('Sudah lewat')
            ->assertSee('Tambah Agenda')
            ->assertSee('Kosongkan jika belum pasti.');
    }

    public function test_agenda_tambah_ubah_dan_hapus(): void
    {
        $this->post(route('admin.agenda.store'), [
            'title' => 'Agenda uji alur', 'tanggal' => '2027-01-10', 'waktu' => '10:30',
            'lokasi' => '', 'status' => 'terbit', 'aksi' => 'terbit', 'deskripsi' => '',
        ])->assertRedirect(route('admin.agenda.index', ['waktu' => 'semua']));

        $this->get(route('admin.agenda.index', ['waktu' => 'semua', 'q' => 'uji alur']))->assertSee('Agenda uji alur')->assertSee('10.30');

        $this->get(route('admin.agenda.index', ['ubah' => 'agenda-1', 'waktu' => 'semua']))
            ->assertSee('Ubah Agenda');

        $this->put(route('admin.agenda.update', 'agenda-1'), [
            'title' => 'Musyawarah Desa Diubah', 'tanggal' => '2026-11-05', 'status' => 'draf',
        ])->assertRedirect();
        $this->get(route('admin.agenda.index', ['waktu' => 'semua', 'q' => 'Diubah']))->assertSee('Musyawarah Desa Diubah');

        $this->delete(route('admin.agenda.destroy', 'agenda-2'))->assertRedirect();
        $this->get(route('admin.agenda.index', ['waktu' => 'semua', 'q' => 'Posyandu']))->assertSee('dari 1 agenda');
    }

    public function test_form_agenda_memvalidasi_isian(): void
    {
        $this->from(route('admin.agenda.index'))->post(route('admin.agenda.store'), ['waktu' => 'bukan-jam'])
            ->assertSessionHasErrors(['title', 'tanggal', 'waktu', 'status']);
    }

    public function test_agenda_draf_tidak_tampil_di_situs_publik(): void
    {
        $this->get(route('berita.index'))->assertOk()->assertDontSee('Agenda draf contoh');
        $this->get(route('home'))->assertOk()->assertDontSee('Agenda draf contoh');
    }
}
