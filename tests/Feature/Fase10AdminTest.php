<?php

namespace Tests\Feature;

use App\Support\Format;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
