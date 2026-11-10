<?php

namespace Tests\Feature;

use App\Enums\Modul;
use App\Enums\StatusTerbit;
use App\Models\Dusun;
use App\Models\Jabatan;
use App\Models\KelompokTani;
use App\Models\Komoditas;
use App\Models\PerangkatDesa;
use App\Models\ProfilDesa;
use App\Models\Umkm;
use App\Models\User;
use App\Support\Format;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\MasukSebagaiAdmin;
use Tests\TestCase;

class Fase6AdminProfilPotensiTest extends TestCase
{
    use MasukSebagaiAdmin, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->masukSebagaiAdmin();
    }

    // ---- Profil ----

    public function test_profil_menampilkan_setiap_tab_dengan_data_database(): void
    {
        ProfilDesa::factory()->create(['sejarah' => 'Naskah Sejarah Uji', 'visi' => 'Visi Uji Tab', 'misi' => ['Misi Tab Satu'], 'sumber_data' => 'Sumber Uji 2026']);

        $this->get(route('admin.profil'))->assertOk()->assertSee('Naskah sejarah')->assertSee('Naskah Sejarah Uji')->assertSee('Terakhir diperbarui');
        $this->get(route('admin.profil', ['tab' => 'visi']))->assertOk()->assertSee('Visi Uji Tab')->assertSee('Misi Tab Satu')->assertSee('Tambah butir misi');
        $this->get(route('admin.profil', ['tab' => 'lokasi']))->assertOk()->assertSee('Pratinjau titik peta')->assertSee('sudah diverifikasi desa');
        $this->get(route('admin.profil', ['tab' => 'data']))->assertOk()->assertSee('Sumber Uji 2026');
        $this->get(route('admin.profil', ['tab' => 'ngawur']))->assertOk()->assertSee('Naskah sejarah');
    }

    public function test_profil_menyimpan_sejarah_visi_misi_dan_data_ringkas_ke_database_dan_publik(): void
    {
        $this->put(route('admin.profil.simpan', 'sejarah'), ['sejarah' => "Paragraf satu.\n\nParagraf dua."])->assertRedirect(route('admin.profil', ['tab' => 'sejarah']));
        $this->put(route('admin.profil.simpan', 'visi'), ['visi' => 'Visi Resmi Uji', 'misi' => ['Misi A', '', '  ', 'Misi B']])
            ->assertRedirect(route('admin.profil', ['tab' => 'visi']))->assertSessionHas('toast', 'Perubahan berhasil disimpan.');
        $this->put(route('admin.profil.simpan', 'data'), ['penduduk' => 5000, 'kk' => 1500, 'luas' => 1200, 'dusun' => 7, 'sumber' => 'Sensus 2026', 'terverifikasi' => '1'])
            ->assertRedirect();

        $profil = ProfilDesa::sole();
        $this->assertSame(['Misi A', 'Misi B'], $profil->misi);
        $this->assertTrue($profil->visi_misi_resmi);
        $this->assertSame(5000, $profil->penduduk);
        $this->assertSame(7, $profil->jumlah_dusun);

        $this->get(route('profil'))->assertOk()->assertSee('Paragraf dua.')->assertSee('Visi Resmi Uji')->assertSee('Misi B')
            ->assertSee('5.000 jiwa')->assertSee('Sensus 2026')->assertDontSee('Menunggu naskah resmi desa');
    }

    public function test_profil_memvalidasi_isian_dan_tab_tidak_dikenal_404(): void
    {
        $this->from(route('admin.profil', ['tab' => 'lokasi']))
            ->put(route('admin.profil.simpan', 'lokasi'), ['lintang' => 'abc'])
            ->assertSessionHasErrors(['alamat', 'lintang', 'bujur']);
        $this->put(route('admin.profil.simpan', 'lokasi'), ['alamat' => 'Jl. A', 'lintang' => 120, 'bujur' => 105, 'email' => 'bukan-email', 'telepon' => 'abc'])
            ->assertSessionHasErrors(['lintang', 'email', 'telepon']);
        $this->put(route('admin.profil.simpan', 'data'), ['penduduk' => -1, 'kk' => 'x', 'luas' => 1, 'dusun' => 1, 'sumber' => ''])
            ->assertSessionHasErrors(['penduduk', 'kk', 'sumber']);
        $this->put(route('admin.profil.simpan', 'visi'), ['visi' => ''])->assertSessionHasErrors('visi');
        $this->put(route('admin.profil.simpan', 'tidak-ada'), [])->assertNotFound();
    }

    public function test_kontak_hanya_terbit_lewat_simpan_terbitkan_dengan_verifikasi(): void
    {
        $lokasi = ['alamat' => 'Jl. Balai Desa 1', 'lintang' => '-5.35', 'bujur' => '105.25', 'telepon' => '0721 123456', 'email' => 'info@desa.test'];

        $this->put(route('admin.profil.simpan', 'lokasi'), $lokasi + ['terverifikasi' => '1', 'aksi' => 'draf'])->assertRedirect();
        $this->assertFalse(ProfilDesa::sole()->kontak_terverifikasi);
        $this->app->forgetScopedInstances();
        $this->get(route('kontak'))->assertOk()->assertDontSee('Jl. Balai Desa 1')->assertDontSee('info@desa.test')->assertDontSee('google.com/maps', false);

        $this->put(route('admin.profil.simpan', 'lokasi'), $lokasi + ['aksi' => 'terbit'])->assertRedirect();
        $this->assertFalse(ProfilDesa::sole()->kontak_terverifikasi);

        $this->put(route('admin.profil.simpan', 'lokasi'), $lokasi + ['terverifikasi' => '1', 'aksi' => 'terbit'])->assertRedirect();
        $this->assertTrue(ProfilDesa::sole()->kontak_terverifikasi);
        $this->app->forgetScopedInstances();
        $this->get(route('kontak'))->assertOk()->assertSee('Jl. Balai Desa 1')->assertSee('info@desa.test')->assertSee('google.com/maps', false);
    }

    // ---- Struktur ----

    private function perangkat(string $nama, int $urutan): PerangkatDesa
    {
        return PerangkatDesa::factory()->create(['nama' => $nama, 'urutan' => $urutan]);
    }

    public function test_struktur_tambah_ubah_hapus_dan_validasi(): void
    {
        $jabatan = Jabatan::factory()->create(['nama' => 'Kepala Desa']);

        $this->get(route('admin.struktur'))->assertOk()->assertSee('Struktur Perangkat Desa')->assertSee('Belum ada perangkat desa');

        $this->post(route('admin.struktur.store'), ['nama' => 'Perangkat Uji', 'jabatan' => 'Kepala Desa', 'tampil' => '1'])->assertRedirect(route('admin.struktur'));
        $p = PerangkatDesa::sole();
        $this->assertSame($jabatan->id, $p->jabatan_id);
        $this->assertSame(1, $p->urutan);
        $this->assertTrue($p->tampil);

        $this->get(route('admin.struktur', ['ubah' => $p->id]))->assertSee('Edit Perangkat');
        $this->put(route('admin.struktur.update', $p->id), ['nama' => 'Nama Baru', 'jabatan' => 'Kepala Desa'])->assertRedirect();
        $this->assertSame('Nama Baru', $p->fresh()->nama);
        $this->assertFalse($p->fresh()->tampil);

        $this->from(route('admin.struktur'))->post(route('admin.struktur.store'), ['nama' => '', 'jabatan' => 'Tidak ada'])->assertSessionHasErrors(['nama']);

        $this->delete(route('admin.struktur.destroy', $p->id))->assertRedirect();
        $this->assertSame(0, PerangkatDesa::count());
        $this->delete(route('admin.struktur.destroy', 999))->assertNotFound();
    }

    public function test_urutan_struktur_naik_turun_dan_batas(): void
    {
        $a = $this->perangkat('A', 1);
        $b = $this->perangkat('B', 2);
        $c = $this->perangkat('C', 3);
        $urutan = fn () => PerangkatDesa::orderBy('urutan')->pluck('nama')->all();

        $this->post(route('admin.struktur.geser', $c->id), ['arah' => 'naik'])->assertRedirect(route('admin.struktur'));
        $this->assertSame(['A', 'C', 'B'], $urutan());

        $this->post(route('admin.struktur.geser', $a->id), ['arah' => 'turun'])->assertRedirect();
        $this->assertSame(['C', 'A', 'B'], $urutan());

        $this->post(route('admin.struktur.geser', $c->id), ['arah' => 'naik'])->assertRedirect();
        $this->post(route('admin.struktur.geser', $b->id), ['arah' => 'turun'])->assertRedirect();
        $this->assertSame(['C', 'A', 'B'], $urutan());

        $this->post(route('admin.struktur.geser', $a->id), ['arah' => 'miring'])->assertSessionHasErrors('arah');
        $this->post(route('admin.struktur.geser', 999), ['arah' => 'naik'])->assertNotFound();
    }

    public function test_urutan_struktur_rapi_walau_ada_nilai_kembar(): void
    {
        $x = $this->perangkat('X', 5);
        $y = $this->perangkat('Y', 5);
        $z = $this->perangkat('Z', 5);

        $this->post(route('admin.struktur.geser', $z->id), ['arah' => 'naik'])->assertRedirect();

        $this->assertSame([1, 2, 3], PerangkatDesa::orderBy('urutan')->pluck('urutan')->all());
        $this->assertSame(['X', 'Z', 'Y'], PerangkatDesa::orderBy('urutan')->pluck('nama')->all());
        $this->assertNotNull($y->fresh());
        $this->assertNotNull($x->fresh());
    }

    public function test_foto_perangkat_harus_persegi_dan_dibersihkan_saat_diganti_atau_dihapus(): void
    {
        Jabatan::factory()->create(['nama' => 'Sekretaris Desa']);
        $data = fn (array $tambah = []) => ['nama' => 'Bu Sekdes', 'jabatan' => 'Sekretaris Desa', 'tampil' => '1'] + $tambah;

        $this->post(route('admin.struktur.store'), $data(['foto' => UploadedFile::fake()->image('persegi.jpg', 300, 200)]))
            ->assertSessionHasErrors('foto');
        $this->assertSame(0, PerangkatDesa::count());

        $this->post(route('admin.struktur.store'), $data(['foto' => UploadedFile::fake()->image('a.jpg', 300, 300)]))->assertRedirect();
        $p = PerangkatDesa::sole();
        $lama = $p->foto_path;
        $this->assertStringStartsWith('perangkat/', $lama);
        Storage::disk('public')->assertExists($lama);
        $this->get(route('profil'))->assertSee('alt="Foto Bu Sekdes, Sekretaris Desa"', false);

        $this->put(route('admin.struktur.update', $p->id), $data(['foto' => UploadedFile::fake()->image('b.png', 400, 400)]))->assertRedirect();
        Storage::disk('public')->assertMissing($lama);
        $baru = $p->fresh()->foto_path;
        Storage::disk('public')->assertExists($baru);

        $this->put(route('admin.struktur.update', $p->id), $data(['hapus_foto' => '1']))->assertRedirect();
        $this->assertNull($p->fresh()->foto_path);
        Storage::disk('public')->assertMissing($baru);
    }

    public function test_perangkat_tersembunyi_tidak_tampil_di_publik(): void
    {
        PerangkatDesa::factory()->create(['nama' => 'Bapak Terlihat', 'tampil' => true]);
        PerangkatDesa::factory()->create(['nama' => 'Ibu Disembunyikan', 'tampil' => false]);

        $this->get(route('profil'))->assertSee('Bapak Terlihat')->assertDontSee('Ibu Disembunyikan');
    }

    // ---- Komoditas ----

    public function test_komoditas_tambah_ubah_hapus_dan_tampil_di_publik_hanya_bila_terbit(): void
    {
        $this->get(route('admin.pertanian'))->assertOk()->assertSee('Belum ada komoditas');

        $this->post(route('admin.pertanian.store'), ['nama' => 'Singkong', 'status' => 'draf', 'musim_panen' => 'Maret'])->assertRedirect(route('admin.pertanian'));
        $komoditas = Komoditas::sole();
        $this->assertSame(StatusTerbit::Draf, $komoditas->status);
        $this->get(route('potensi'))->assertDontSee('Singkong');

        $this->put(route('admin.pertanian.update', $komoditas->id), ['nama' => 'Singkong Mentega', 'status' => 'terbit', 'unggulan' => '1', 'deskripsi' => 'Enak.'])->assertRedirect();
        $this->assertTrue($komoditas->fresh()->unggulan);
        $this->get(route('potensi'))->assertSee('Singkong Mentega');
        $this->get(route('home'))->assertSee('Singkong Mentega');
        $this->get(route('admin.pertanian', ['ubah' => $komoditas->id]))->assertSee('Ubah Komoditas');
        $this->get(route('admin.pertanian', ['status' => 'draf']))->assertDontSee('Singkong Mentega');

        $this->from(route('admin.pertanian'))->post(route('admin.pertanian.store'), [])->assertSessionHasErrors(['nama', 'status']);
        $this->delete(route('admin.pertanian.destroy', $komoditas->id))->assertRedirect();
        $this->assertSame(0, Komoditas::count());
        $this->delete(route('admin.pertanian.destroy', 999))->assertNotFound();
    }

    public function test_foto_komoditas_dengan_alt_tampil_di_publik_dan_dibersihkan(): void
    {
        $this->post(route('admin.pertanian.store'), ['nama' => 'Jagung', 'status' => 'terbit', 'foto' => UploadedFile::fake()->image('j.jpg')])->assertSessionHasErrors('alt');

        $this->post(route('admin.pertanian.store'), ['nama' => 'Jagung', 'status' => 'terbit', 'foto' => UploadedFile::fake()->image('j.jpg'), 'alt' => 'Ladang jagung siap panen'])->assertRedirect();
        $k = Komoditas::sole();
        Storage::disk('public')->assertExists($k->foto_path);
        $this->get(route('potensi'))->assertSee('alt="Ladang jagung siap panen"', false);

        $this->post(route('admin.pertanian.store'), ['nama' => 'X', 'status' => 'draf', 'foto' => UploadedFile::fake()->image('besar.jpg')->size(3000), 'alt' => 'x'])->assertSessionHasErrors('foto');

        $path = $k->foto_path;
        $this->delete(route('admin.pertanian.destroy', $k->id))->assertRedirect();
        Storage::disk('public')->assertMissing($path);
    }

    public function test_menghapus_komoditas_tidak_menghapus_kelompok_tani_terkait(): void
    {
        $k = Komoditas::factory()->create();
        $g = KelompokTani::factory()->create(['komoditas_id' => $k->id]);

        $this->delete(route('admin.pertanian.destroy', $k->id))->assertRedirect();

        $this->assertNull($g->fresh()->komoditas_id);
    }

    // ---- Kelompok tani ----

    public function test_kontak_kelompok_tani_wajib_diisi_bila_disetujui_untuk_publikasi(): void
    {
        $komoditas = Komoditas::factory()->create();
        $dusun = Dusun::factory()->create();
        $data = ['nama' => 'Kelompok Uji', 'komoditas' => $komoditas->id, 'dusun' => $dusun->id];

        $this->get(route('admin.kelompok-tani'))->assertOk()->assertSee('Hanya tampil di publik jika disetujui.');

        $this->from(route('admin.kelompok-tani'))->post(route('admin.kelompok-tani.store'), $data + ['disetujui' => '1'])->assertSessionHasErrors('kontak');

        $this->post(route('admin.kelompok-tani.store'), $data + ['kontak' => '0812-3456-7890', 'disetujui' => '1'])->assertRedirect(route('admin.kelompok-tani'));
        $kelompok = KelompokTani::sole();
        $this->assertTrue($kelompok->kontak_disetujui);
        $this->get(route('admin.kelompok-tani'))->assertSee('Kelompok Uji')->assertSee('Disetujui');
        $this->get(route('potensi'))->assertSee('0812-3456-7890');

        $this->put(route('admin.kelompok-tani.update', $kelompok->id), $data + ['kontak' => '0812-3456-7890'])->assertRedirect();
        $this->assertFalse($kelompok->fresh()->kontak_disetujui);
        $this->get(route('potensi'))->assertDontSee('0812-3456-7890');
    }

    public function test_kelompok_tani_menolak_komoditas_atau_dusun_tak_dikenal_dan_bisa_dihapus(): void
    {
        $this->from(route('admin.kelompok-tani'))->post(route('admin.kelompok-tani.store'), ['nama' => 'X', 'komoditas' => 999, 'dusun' => 999])
            ->assertSessionHasErrors(['komoditas', 'dusun']);
        $this->post(route('admin.kelompok-tani.store'), ['nama' => 'X', 'komoditas' => 'jagung', 'dusun' => 'Dusun 1', 'kontak' => 'abc'])
            ->assertSessionHasErrors(['komoditas', 'dusun', 'kontak']);

        $g = KelompokTani::factory()->create();
        $this->get(route('admin.kelompok-tani', ['q' => $g->nama]))->assertSee($g->nama);
        $this->delete(route('admin.kelompok-tani.destroy', $g->id))->assertRedirect();
        $this->assertSame(0, KelompokTani::count());
        $this->put(route('admin.kelompok-tani.update', 999), ['nama' => 'X', 'komoditas' => Komoditas::factory()->create()->id, 'dusun' => Dusun::factory()->create()->id])->assertNotFound();
    }

    // ---- UMKM ----

    public function test_nomor_whatsapp_dinormalkan_dan_divalidasi(): void
    {
        $this->assertSame('6281234567890', Format::nomorWa('0812 3456 7890'));
        $this->assertSame('6281234567890', Format::nomorWa('+62 812-3456-7890'));
        $this->assertSame('6281234567890', Format::nomorWa('81234567890'));
        $this->assertNull(Format::nomorWa('021123'));
        $this->assertNull(Format::nomorWa(''));

        $this->post(route('admin.umkm.store'), ['nama' => 'Usaha Uji', 'produk' => 'Produk', 'whatsapp' => '0812 3456 7890'])->assertRedirect(route('admin.umkm'));
        $this->assertSame('6281234567890', Umkm::sole()->whatsapp);

        $this->from(route('admin.umkm'))->post(route('admin.umkm.store'), ['whatsapp' => 'abc'])->assertSessionHasErrors(['nama', 'produk', 'whatsapp']);
        $this->post(route('admin.umkm.store'), ['nama' => 'Y', 'produk' => 'Z', 'whatsapp' => '12345678'])->assertSessionHasErrors('whatsapp');
    }

    public function test_whatsapp_dan_foto_umkm_tampil_publik_hanya_bila_disetujui(): void
    {
        $this->post(route('admin.umkm.store'), [
            'nama' => 'Usaha Belum Setuju', 'produk' => 'Keripik', 'whatsapp' => '081111111111', 'status' => 'terbit',
            'foto' => UploadedFile::fake()->image('p.jpg'), 'alt' => 'Keripik jagung',
        ])->assertRedirect();
        $this->post(route('admin.umkm.store'), [
            'nama' => 'Usaha Sudah Setuju', 'produk' => 'Anyaman', 'whatsapp' => '082222222222', 'disetujui' => '1', 'status' => 'terbit',
            'foto' => UploadedFile::fake()->image('q.jpg'), 'alt' => 'Anyaman bambu',
        ])->assertRedirect();

        $belum = Umkm::where('nama', 'Usaha Belum Setuju')->sole();
        Storage::disk('public')->assertExists($belum->foto_path);

        $html = $this->get(route('potensi'))->assertOk()->getContent();
        $this->assertStringContainsString('6282222222222', $html);
        $this->assertStringNotContainsString('6281111111111', $html);
        $this->assertStringContainsString('alt="Anyaman bambu"', $html);
        $this->assertStringNotContainsString('alt="Keripik jagung"', $html);
        $this->assertStringNotContainsString($belum->foto_path, $html);
    }

    public function test_umkm_ubah_status_cari_dan_hapus_membersihkan_foto(): void
    {
        $this->post(route('admin.umkm.store'), ['nama' => 'Dapur Uji', 'produk' => 'Keripik', 'status' => 'draf', 'foto' => UploadedFile::fake()->image('p.jpg'), 'alt' => 'Foto'])->assertRedirect();
        $u = Umkm::sole();
        $this->assertSame(StatusTerbit::Draf, $u->status);
        $this->get(route('potensi'))->assertDontSee('Dapur Uji');

        $this->put(route('admin.umkm.update', $u->id), ['nama' => 'Dapur Uji', 'produk' => 'Keripik', 'status' => 'terbit'])->assertRedirect();
        $this->assertSame(StatusTerbit::Terbit, $u->fresh()->status);
        $this->assertNotNull($u->fresh()->foto_path);
        $this->get(route('potensi'))->assertSee('Dapur Uji');
        $this->get(route('admin.umkm', ['q' => 'Dapur', 'ubah' => $u->id]))->assertSee('Edit UMKM');
        $this->get(route('admin.umkm', ['q' => 'tidak-ada']))->assertDontSee('Dapur Uji');

        $path = $u->fresh()->foto_path;
        $this->delete(route('admin.umkm.destroy', $u->id))->assertRedirect(route('admin.umkm'));
        $this->assertSame(0, Umkm::count());
        Storage::disk('public')->assertMissing($path);
    }

    // ---- Otorisasi ----

    public function test_modul_profil_dan_potensi_terpisah_untuk_staf(): void
    {
        $this->actingAs(User::factory()->denganModul([Modul::Profil])->create());
        $this->put(route('admin.profil.simpan', 'sejarah'), ['sejarah' => 'Boleh'])->assertRedirect();
        $this->post(route('admin.umkm.store'), ['nama' => 'X', 'produk' => 'Y'])->assertForbidden();
        $this->post(route('admin.pertanian.store'), ['nama' => 'X', 'status' => 'draf'])->assertForbidden();

        $this->actingAs(User::factory()->denganModul([Modul::Potensi])->create());
        $this->post(route('admin.umkm.store'), ['nama' => 'Usaha', 'produk' => 'Produk'])->assertRedirect();
        $this->put(route('admin.profil.simpan', 'sejarah'), ['sejarah' => 'Tidak boleh'])->assertForbidden();
        $this->post(route('admin.struktur.geser', 1), ['arah' => 'naik'])->assertForbidden();

        $this->assertSame('Boleh', ProfilDesa::sole()->sejarah);
        $this->assertSame(1, Umkm::count());
    }
}
