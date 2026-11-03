<?php

namespace Tests\Feature;

use App\Support\Format;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\Concerns\MasukSebagaiAdmin;
use Tests\TestCase;

class Fase10AdminTest extends TestCase
{
    use MasukSebagaiAdmin, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->masukSebagaiAdmin();
    }

    public function test_profil_menampilkan_setiap_tab(): void
    {
        $this->get(route('admin.profil'))->assertOk()->assertSee('Naskah sejarah')->assertSee('Terakhir diperbarui');
        $this->get(route('admin.profil', ['tab' => 'visi']))->assertOk()->assertSee('Tambah butir misi')->assertSee('Salin sesuai dokumen RPJMDes');
        $this->get(route('admin.profil', ['tab' => 'lokasi']))->assertOk()->assertSee('Pratinjau titik peta')->assertSee('sudah diverifikasi pemerintah desa');
        $this->get(route('admin.profil', ['tab' => 'data']))->assertOk()->assertSee('Sumber data');
    }

    public function test_profil_memvalidasi_dan_menyimpan(): void
    {
        $this->from(route('admin.profil', ['tab' => 'lokasi']))
            ->put(route('admin.profil.simpan', 'lokasi'), ['lintang' => 'abc'])
            ->assertSessionHasErrors(['alamat', 'lintang', 'bujur']);

        $this->put(route('admin.profil.simpan', 'visi'), ['visi' => 'Visi uji', 'misi' => ['Misi satu', 'Misi dua']])
            ->assertRedirect(route('admin.profil', ['tab' => 'visi']))
            ->assertSessionHas('toast', 'Perubahan berhasil disimpan.');
        $this->get(route('admin.profil', ['tab' => 'visi']))->assertSee('Misi dua');

        $this->put(route('admin.profil.simpan', 'tidak-ada'), [])->assertNotFound();
    }

    public function test_struktur_tambah_geser_dan_hapus(): void
    {
        $this->get(route('admin.struktur'))->assertOk()->assertSee('Struktur Perangkat Desa')->assertSee('Tampil');

        $this->post(route('admin.struktur.store'), ['nama' => 'Perangkat Uji', 'jabatan' => 'Kepala Desa', 'tampil' => '1'])
            ->assertRedirect(route('admin.struktur'));
        $this->get(route('admin.struktur'))->assertSee('Perangkat Uji');

        $this->post(route('admin.struktur.geser', 'perangkat-2'), ['arah' => 'naik'])->assertRedirect();
        $this->post(route('admin.struktur.geser', 'perangkat-2'), ['arah' => 'miring'])->assertSessionHasErrors('arah');

        $this->delete(route('admin.struktur.destroy', 'perangkat-1'))->assertRedirect();
        $this->get(route('admin.struktur', ['ubah' => 'perangkat-1']))->assertDontSee('Edit Perangkat');

        $this->from(route('admin.struktur'))->post(route('admin.struktur.store'), ['nama' => '', 'jabatan' => 'Tidak ada'])
            ->assertSessionHasErrors(['nama', 'jabatan']);
    }

    public function test_apbdes_menolak_angka_bukan_angka_dan_menyimpan_yang_benar(): void
    {
        $this->get(route('admin.apbdes'))->assertOk()->assertSee('Kelola APBDes')->assertSee('APBDes 2026')->assertSee('2 PDF');

        $this->from(route('admin.apbdes'))->put(route('admin.apbdes.update', '2026'), [
            'tahun' => 2026, 'periode' => 'Jan – Des', 'pendapatan' => '12.500.000', 'belanja' => '12.500.000,5x', 'status' => 'draf',
        ])->assertSessionHasErrors(['belanja' => 'Masukkan angka saja tanpa huruf.']);

        $this->put(route('admin.apbdes.update', '2026'), [
            'tahun' => 2026, 'periode' => 'Jan – Des', 'pendapatan' => '12.500.000', 'belanja' => '11.000.000,50', 'status' => 'draf', 'aksi' => 'terbit',
        ])->assertRedirect()->assertSessionHas('toast');
        $this->get(route('admin.apbdes', ['periode' => '2026']))->assertSee('12.500.000');
    }

    public function test_apbdes_menolak_berkas_bukan_pdf(): void
    {
        $this->from(route('admin.apbdes'))->post(route('admin.apbdes.store'), [
            'tahun' => 2027, 'periode' => 'Jan – Des', 'pendapatan' => '1', 'belanja' => '1', 'status' => 'draf',
            'dokumen' => UploadedFile::fake()->create('dokumen.exe', 10, 'application/x-msdownload'),
        ])->assertSessionHasErrors(['dokumen' => 'Ditolak: hanya PDF yang diizinkan. Pilih berkas lain.']);
    }

    public function test_pertanian_menampilkan_komoditas_dan_kelompok_lalu_menyimpan(): void
    {
        $this->get(route('admin.pertanian'))->assertOk()->assertSee('Jagung')->assertSee('Menunggu konfirmasi')
            ->assertSee('Kontak tidak dipublikasikan')->assertSee('bukti persetujuan');

        $this->post(route('admin.pertanian.store'), ['nama' => 'Singkong', 'status' => 'draf'])->assertRedirect(route('admin.pertanian'));
        $this->get(route('admin.pertanian'))->assertSee('Singkong');

        $this->from(route('admin.pertanian'))->post(route('admin.pertanian.store'), [])->assertSessionHasErrors(['nama', 'status']);
        $this->delete(route('admin.pertanian.destroy', 'tidak-ada'))->assertNotFound();
    }

    public function test_kontak_kelompok_tani_wajib_diisi_bila_disetujui_untuk_publikasi(): void
    {
        $this->get(route('admin.kelompok-tani'))->assertOk()->assertSee('Belum disetujui')->assertSee('Hanya tampil di publik jika disetujui.');

        $this->from(route('admin.kelompok-tani'))->post(route('admin.kelompok-tani.store'), [
            'nama' => 'Kelompok Uji', 'komoditas' => 'Jagung', 'dusun' => 'Dusun 1', 'disetujui' => '1',
        ])->assertSessionHasErrors('kontak');

        $this->post(route('admin.kelompok-tani.store'), [
            'nama' => 'Kelompok Uji', 'komoditas' => 'Jagung', 'dusun' => 'Dusun 1', 'kontak' => '0812-3456-7890', 'disetujui' => '1',
        ])->assertRedirect(route('admin.kelompok-tani'));
        $this->get(route('admin.kelompok-tani'))->assertSee('Kelompok Uji')->assertSee('Disetujui');
    }

    public function test_umkm_menyimpan_dan_menghapus(): void
    {
        $this->get(route('admin.umkm'))->assertOk()->assertSee('Dapur Jagung Bersama')->assertSee('Dengan izin pemilik usaha');

        $this->post(route('admin.umkm.store'), ['nama' => 'Usaha Uji', 'produk' => 'Produk uji', 'disetujui' => '1', 'whatsapp' => '0812 3456 7890'])
            ->assertRedirect(route('admin.umkm'));
        $this->get(route('admin.umkm'))->assertSee('Usaha Uji');

        $this->from(route('admin.umkm'))->post(route('admin.umkm.store'), ['whatsapp' => 'abc'])->assertSessionHasErrors(['nama', 'produk', 'whatsapp']);
        $this->delete(route('admin.umkm.destroy', 'umkm-1'))->assertRedirect(route('admin.umkm'));
        $this->get(route('admin.umkm'))->assertDontSee('Dapur Jagung Bersama');
    }

    public function test_aspirasi_filter_detail_dan_ubah_status(): void
    {
        $this->get(route('admin.aspirasi.index'))->assertOk()
            ->assertSee('Baru (3)')->assertSee('NIK: tidak diisi pengirim')->assertSee('Riwayat penanganan');

        $this->get(route('admin.aspirasi.index', ['status' => 'selesai']))->assertOk()->assertSee('Pengirim E')->assertDontSee('Pengirim A');

        $this->put(route('admin.aspirasi.status', 'asp-1'), ['status' => 'baru', 'aksi' => 'selesai', 'catatan' => 'Sudah diteruskan ke kaur.'])
            ->assertRedirect(route('admin.aspirasi.index', ['id' => 'asp-1']))
            ->assertSessionHas('toast', 'Perubahan berhasil disimpan.');

        $this->get(route('admin.aspirasi.index', ['id' => 'asp-1']))->assertSee('Baru (2)')->assertSee('Status diubah menjadi Selesai')->assertSee('Sudah diteruskan ke kaur.');
        $this->get(route('admin.dashboard'))->assertSee('Aspirasi belum diproses');

        $this->put(route('admin.aspirasi.status', 'tidak-ada'), ['status' => 'baru'])->assertNotFound();
        $this->put(route('admin.aspirasi.status', 'asp-2'), ['status' => 'rahasia'])->assertSessionHasErrors('status');
    }

    public function test_aspirasi_kosong_per_filter(): void
    {
        foreach (['asp-1', 'asp-2', 'asp-3'] as $id) {
            $this->put(route('admin.aspirasi.status', $id), ['status' => 'selesai']);
        }

        $this->get(route('admin.aspirasi.index', ['status' => 'baru']))->assertOk()
            ->assertSee('Tidak ada aspirasi baru')->assertSee('Baru (0)');
    }

    public function test_nik_selalu_disamarkan(): void
    {
        $this->assertSame('•••• •••• •••• 0001', Format::nikSamar('1871010101010001'));
        $this->assertNull(Format::nikSamar(null));
        $this->assertNull(Format::nikSamar(''));
    }

    public function test_aspirasi_tidak_pernah_tampil_di_halaman_publik(): void
    {
        foreach (['home', 'profil', 'apbdes', 'potensi', 'berita.index', 'aspirasi.create', 'kontak'] as $nama) {
            $this->get(route($nama))->assertOk()
                ->assertDontSee('jalan dusun yang rusak')
                ->assertDontSee('Pengirim A')
                ->assertDontSee('lampu jalan yang padam');
        }
    }
}
