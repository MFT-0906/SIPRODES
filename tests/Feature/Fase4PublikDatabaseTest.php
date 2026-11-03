<?php

namespace Tests\Feature;

use App\Enums\JenisAnggaran;
use App\Models\Agenda;
use App\Models\ApbdesDokumen;
use App\Models\ApbdesPeriode;
use App\Models\ApbdesRincian;
use App\Models\Berita;
use App\Models\Dusun;
use App\Models\KelompokTani;
use App\Models\Komoditas;
use App\Models\PerangkatDesa;
use App\Models\ProfilDesa;
use App\Models\Umkm;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class Fase4PublikDatabaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_draf_tidak_bocor_lewat_daftar_detail_pencarian_dan_hitungan(): void
    {
        Berita::factory()->create(['judul' => 'Berita Tayang', 'slug' => 'berita-tayang']);
        Berita::factory()->draf()->create(['judul' => 'Rahasia Draf Satu', 'slug' => 'rahasia-draf-satu', 'ringkasan' => 'kata-kunci-draf']);
        Berita::factory()->draf()->create(['judul' => 'Rahasia Draf Dua', 'kategori' => 'KategoriDraf']);
        Agenda::factory()->draf()->create(['judul' => 'Agenda Rahasia']);
        Komoditas::factory()->draf()->create(['nama' => 'Komoditas Rahasia']);
        Umkm::factory()->draf()->create(['nama' => 'Usaha Rahasia']);
        ApbdesPeriode::factory()->draf()->create(['tahun' => 2031]);

        $this->get('/')->assertOk()->assertSee('Berita Tayang')->assertDontSee('Rahasia Draf')->assertDontSee('Agenda Rahasia');
        $this->get('/berita')->assertOk()->assertSee('Berita Tayang')->assertDontSee('Rahasia Draf')
            ->assertDontSee('KategoriDraf')->assertDontSee('Agenda Rahasia');
        $this->get('/berita?q=kata-kunci-draf')->assertOk()->assertDontSee('Rahasia Draf')->assertSee('Tidak ada berita yang cocok');
        $this->get('/berita?kategori=KategoriDraf')->assertOk()->assertDontSee('Rahasia Draf Dua');
        $this->get('/berita/rahasia-draf-satu')->assertNotFound();
        $this->get('/potensi-desa')->assertOk()->assertDontSee('Komoditas Rahasia')->assertDontSee('Usaha Rahasia');
        $this->get('/potensi-desa?q=Rahasia')->assertOk()->assertDontSee('Komoditas Rahasia')->assertDontSee('Usaha Rahasia');
        $this->get('/transparansi-apbdes?tahun=2031')->assertOk()->assertSee('Data APBDes belum dipublikasikan')->assertDontSee('2031');
    }

    public function test_hitungan_paginasi_tidak_menghitung_draf(): void
    {
        Berita::factory()->count(5)->create();
        Berita::factory()->draf()->count(7)->create();
        Umkm::factory()->count(3)->create();
        Umkm::factory()->draf()->count(9)->create();

        $this->get('/berita')->assertOk()->assertSee('page=2', false)->assertDontSee('page=3', false);
        $this->get('/potensi-desa')->assertOk()->assertSee('dari 3 UMKM');
    }

    public function test_berita_paginasi_filter_kategori_dan_pencarian_dipertahankan(): void
    {
        Berita::factory()->count(6)->create(['kategori' => 'Kegiatan']);
        Berita::factory()->create(['kategori' => 'Pertanian', 'judul' => 'Panen Raya Jagung', 'tanggal' => now()->addDay()]);

        $this->get('/berita?kategori=Kegiatan')->assertOk()->assertDontSee('Panen Raya Jagung')
            ->assertSeeInOrder(['page=2'], false)->assertSee('kategori=Kegiatan', false);
        $this->get('/berita?kategori=Kegiatan&page=2')->assertOk()->assertDontSee('Panen Raya Jagung');
        $this->get('/berita?q=Panen+Raya')->assertOk()->assertSee('Panen Raya Jagung');
        $this->get('/berita?kategori=TidakAda')->assertOk()->assertSee('Panen Raya Jagung');
    }

    public function test_pencarian_dengan_karakter_khusus_tidak_error_dan_tidak_menyuntikkan_sql(): void
    {
        Berita::factory()->create(['judul' => 'Berita Biasa']);
        Umkm::factory()->create(['nama' => 'Usaha Biasa']);

        foreach (["' OR '1'='1", '%', '_', '!', "'; DROP TABLE berita; --", '\\', '%%%%', '100%'] as $kata) {
            $this->get('/berita?q='.urlencode($kata))->assertOk()->assertDontSee('Berita Biasa');
            $this->get('/potensi-desa?q='.urlencode($kata))->assertOk()->assertDontSee('Usaha Biasa');
        }

        $this->assertSame(1, Berita::count());
    }

    public function test_pencarian_potensi_lintas_halaman_dan_mempertahankan_kata_kunci(): void
    {
        foreach (range(1, 9) as $i) {
            Umkm::factory()->create(['nama' => sprintf('Usaha %02d', $i), 'produk' => 'keripik']);
        }
        Umkm::factory()->create(['nama' => 'Zzz Terakhir', 'produk' => 'anyaman bambu']);

        // "Zzz Terakhir" berada di halaman 3 tanpa pencarian, tetapi harus ditemukan dari halaman 1.
        $this->get('/potensi-desa?q=bambu')->assertOk()->assertSee('Zzz Terakhir')->assertDontSee('Usaha 01');
        $this->get('/potensi-desa?q=keripik')->assertOk()->assertSee('dari 9 UMKM')
            ->assertSee('q=keripik', false);
        $this->get('/potensi-desa?q=tidak-ada-sama-sekali')->assertOk()->assertSee('Tidak ada usaha yang cocok');
    }

    public function test_pencarian_potensi_mencakup_komoditas_dan_kelompok_tani(): void
    {
        $jagung = Komoditas::factory()->create(['nama' => 'Jagung Manis', 'deskripsi' => 'Jagung unggulan.']);
        Komoditas::factory()->create(['nama' => 'Singkong']);
        KelompokTani::factory()->create(['nama' => 'Kelompok Makmur', 'komoditas_id' => $jagung->id]);
        KelompokTani::factory()->create(['nama' => 'Kelompok Lain']);

        $this->get('/potensi-desa?q=Jagung')->assertOk()
            ->assertSee('Jagung Manis')->assertDontSee('Singkong')
            ->assertSee('Kelompok Makmur')->assertDontSee('Kelompok Lain');
    }

    public function test_kontak_tidak_tampil_sebelum_diverifikasi(): void
    {
        ProfilDesa::factory()->create([
            'alamat' => 'Jl. Rahasia No. 1', 'telepon' => '0721123456', 'email' => 'rahasia@desa.test',
            'jam_layanan' => 'Jam Rahasia', 'lintang' => -5.35, 'bujur' => 105.25, 'kontak_terverifikasi' => false,
        ]);

        foreach (['/', '/profil-desa', '/kontak-lokasi'] as $url) {
            $this->get($url)->assertOk()
                ->assertDontSee('Jl. Rahasia')->assertDontSee('0721123456')->assertDontSee('rahasia@desa.test')
                ->assertDontSee('Jam Rahasia')->assertDontSee('google.com/maps', false)
                ->assertDontSee('href="tel:', false)->assertDontSee('href="mailto:', false);
        }
    }

    public function test_kontak_tampil_setelah_diverifikasi(): void
    {
        ProfilDesa::factory()->terverifikasi()->create([
            'alamat' => 'Jl. Balai Desa No. 1', 'telepon' => '0721123456', 'email' => 'info@desa.test',
        ]);

        $this->get('/kontak-lokasi')->assertOk()
            ->assertSee('Jl. Balai Desa No. 1')->assertSee('0721123456')->assertSee('info@desa.test')
            ->assertSee('google.com/maps', false);
        $this->get('/')->assertOk()->assertSee('Kontak Resmi');
    }

    public function test_kontak_kelompok_tani_dan_whatsapp_umkm_mengikuti_persetujuan(): void
    {
        KelompokTani::factory()->create(['nama' => 'Kelompok A', 'kontak' => '081111111111', 'kontak_disetujui' => false]);
        KelompokTani::factory()->create(['nama' => 'Kelompok B', 'kontak' => '082222222222', 'kontak_disetujui' => true]);
        Umkm::factory()->create(['nama' => 'Usaha A', 'whatsapp' => '6283333333333', 'whatsapp_disetujui' => false]);
        Umkm::factory()->create(['nama' => 'Usaha B', 'whatsapp' => '6284444444444', 'whatsapp_disetujui' => true]);

        $this->get('/potensi-desa')->assertOk()
            ->assertDontSee('081111111111')->assertSee('082222222222')
            ->assertDontSee('6283333333333')->assertSee('6284444444444');
        $this->get('/')->assertOk()->assertDontSee('6283333333333')->assertSee('6284444444444');
    }

    public function test_profil_memakai_data_database_dan_menyembunyikan_perangkat_tidak_tampil(): void
    {
        ProfilDesa::factory()->create([
            'sejarah' => "Paragraf utama sejarah.\n\nParagraf kedua sejarah.", 'visi' => 'Visi Uji Desa',
            'misi' => ['Misi Uji Satu', 'Misi Uji Dua'], 'visi_misi_resmi' => true, 'penduduk' => 1234, 'data_terverifikasi' => true,
        ]);
        PerangkatDesa::factory()->create(['nama' => 'Bapak Tampil']);
        PerangkatDesa::factory()->create(['nama' => 'Ibu Tersembunyi', 'tampil' => false]);

        $this->get('/profil-desa')->assertOk()
            ->assertSee('Paragraf utama sejarah.')->assertSee('Paragraf kedua sejarah.')
            ->assertSee('Visi Uji Desa')->assertSee('Misi Uji Dua')->assertSee('1.234 jiwa')
            ->assertDontSee('Menunggu naskah resmi desa')
            ->assertSee('Bapak Tampil')->assertDontSee('Ibu Tersembunyi');
    }

    public function test_profil_kosong_tidak_error_dan_diberi_penanda(): void
    {
        $this->get('/profil-desa')->assertOk()->assertSee('Menunggu naskah resmi desa');
        $this->get('/')->assertOk();
        $this->get('/kontak-lokasi')->assertOk();
    }

    public function test_apbdes_total_dihitung_dari_rincian_dan_hanya_periode_terbit(): void
    {
        $terbit = ApbdesPeriode::factory()->create(['tahun' => 2030]);
        ApbdesRincian::factory()->create(['periode_id' => $terbit->id, 'uraian' => 'Dana Desa', 'anggaran' => 1_000_000]);
        ApbdesRincian::factory()->create(['periode_id' => $terbit->id, 'uraian' => 'Alokasi', 'anggaran' => 2_500_000]);
        ApbdesRincian::factory()->create(['periode_id' => $terbit->id, 'jenis' => JenisAnggaran::Belanja, 'uraian' => 'Pembangunan', 'anggaran' => 3_000_000]);
        ApbdesDokumen::factory()->create(['periode_id' => $terbit->id, 'judul' => 'Perdes APBDes 2030']);
        ApbdesPeriode::factory()->draf()->create(['tahun' => 2031]);

        $this->get('/transparansi-apbdes')->assertOk()
            ->assertSee('Rp 3.500.000')->assertSee('Rp 3.000.000')->assertSee('Perdes APBDes 2030')
            ->assertSee('Berkas belum diunggah')
            ->assertDontSee('2031')->assertDontSee('Diagram belum tersedia');
        $this->get('/transparansi-apbdes?tahun=2031')->assertOk()->assertSee('2030');
    }

    public function test_dokumen_apbdes_belum_menyediakan_tautan_ke_berkas_privat(): void
    {
        $periode = ApbdesPeriode::factory()->create(['tahun' => 2030]);
        ApbdesDokumen::factory()->create(['periode_id' => $periode->id, 'judul' => 'Perdes', 'path' => 'apbdes/rahasia.pdf', 'ukuran' => 2048]);

        $this->get('/transparansi-apbdes')->assertOk()->assertDontSee('rahasia.pdf')->assertDontSee('storage/');
    }

    public function test_jumlah_query_halaman_publik_tidak_bertambah_seiring_jumlah_data(): void
    {
        $hitung = function (string $url): int {
            DB::flushQueryLog();
            DB::enableQueryLog();
            $this->get($url)->assertOk();
            $jumlah = count(DB::getQueryLog());
            DB::disableQueryLog();

            return $jumlah;
        };

        Berita::factory()->count(2)->create();
        Umkm::factory()->count(2)->create();
        $dusun = Dusun::factory()->create();
        KelompokTani::factory()->count(2)->create(['dusun_id' => $dusun->id, 'komoditas_id' => Komoditas::factory()]);
        PerangkatDesa::factory()->count(2)->create();
        $awal = array_map($hitung, ['/', '/berita', '/potensi-desa', '/profil-desa']);

        Berita::factory()->count(10)->create();
        Umkm::factory()->count(10)->create();
        KelompokTani::factory()->count(10)->create(['dusun_id' => $dusun->id, 'komoditas_id' => Komoditas::factory()]);
        PerangkatDesa::factory()->count(10)->create();
        $akhir = array_map($hitung, ['/', '/berita', '/potensi-desa', '/profil-desa']);

        foreach ($awal as $i => $jumlah) {
            $this->assertLessThanOrEqual($jumlah, $akhir[$i], 'Jumlah query bertambah seiring data (kemungkinan N+1).');
        }
    }
}
