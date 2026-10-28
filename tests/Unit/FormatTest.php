<?php

namespace Tests\Unit;

use App\Support\Format;
use PHPUnit\Framework\TestCase;

class FormatTest extends TestCase
{
    public function test_rupiah_memakai_pemisah_titik(): void
    {
        $this->assertSame('Rp 1.234.567', Format::rupiah(1234567));
        $this->assertSame('Rp 0', Format::rupiah(0));
    }

    public function test_rupiah_kosong_ditampilkan_sebagai_tanda_hubung(): void
    {
        $this->assertSame('Rp —', Format::rupiah(null));
        $this->assertSame('Rp —', Format::rupiah(''));
    }

    public function test_persen_memakai_koma_desimal(): void
    {
        $this->assertSame('12,5%', Format::persen(12.5));
        $this->assertSame('—', Format::persen(null));
    }

    public function test_tanggal_berformat_indonesia(): void
    {
        $this->assertSame('18 Okt 2026', Format::tanggal('2026-10-18'));
        $this->assertSame('5 Januari 2026', Format::tanggal('2026-01-05', true));
        $this->assertSame('—', Format::tanggal(null));
    }

    public function test_ukuran_berkas(): void
    {
        $this->assertSame('2,4 MB', Format::ukuranBerkas((int) (2.4 * 1024 * 1024)));
        $this->assertSame('500 KB', Format::ukuranBerkas(500 * 1024));
        $this->assertSame('—', Format::ukuranBerkas(null));
    }
}
