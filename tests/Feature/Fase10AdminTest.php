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
