<?php

namespace Tests\Feature;

use App\Enums\JenisAnggaran;
use App\Enums\Modul;
use App\Enums\Peran;
use App\Enums\StatusAspirasi;
use App\Enums\StatusTerbit;
use App\Models\Agenda;
use App\Models\ApbdesDokumen;
use App\Models\ApbdesPeriode;
use App\Models\ApbdesRincian;
use App\Models\Aspirasi;
use App\Models\AspirasiRiwayat;
use App\Models\Berita;
use App\Models\Dusun;
use App\Models\Jabatan;
use App\Models\KelompokTani;
use App\Models\Komoditas;
use App\Models\LogAktivitas;
use App\Models\PerangkatDesa;
use App\Models\ProfilDesa;
use App\Models\Umkm;
use App\Models\User;
use Database\Seeders\AdminAwalSeeder;
use Database\Seeders\DataContohSeeder;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class Fase1SkemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_semua_factory_dapat_membuat_model(): void
    {
        foreach ([
            Berita::class, Agenda::class, Komoditas::class, KelompokTani::class, Umkm::class,
            ApbdesPeriode::class, ApbdesRincian::class, ApbdesDokumen::class, Aspirasi::class,
            AspirasiRiwayat::class, LogAktivitas::class, PerangkatDesa::class, ProfilDesa::class,
            Dusun::class, Jabatan::class, User::class,
        ] as $kelas) {
            $this->assertTrue($kelas::factory()->create()->exists, "Factory {$kelas} gagal.");
        }
    }

    public function test_scope_terbit_hanya_mengembalikan_konten_terbit(): void
    {
        Berita::factory()->create();
        Berita::factory()->draf()->create();
        Agenda::factory()->create();
        Agenda::factory()->draf()->create();
        Komoditas::factory()->draf()->create();
        Umkm::factory()->create();
        Umkm::factory()->draf()->create();
        ApbdesPeriode::factory()->draf()->create();

        $this->assertSame(1, Berita::terbit()->count());
        $this->assertSame(1, Berita::draf()->count());
        $this->assertSame(1, Agenda::terbit()->count());
        $this->assertSame(0, Komoditas::terbit()->count());
        $this->assertSame(1, Umkm::terbit()->count());
        $this->assertSame(0, ApbdesPeriode::terbit()->count());
        $this->assertSame(StatusTerbit::Terbit, Berita::terbit()->first()->status);
    }

    public function test_agenda_mendatang_dan_lewat(): void
    {
        Agenda::factory()->create();
        Agenda::factory()->lewat()->create();

        $this->assertSame(1, Agenda::mendatang()->count());
        $this->assertSame(1, Agenda::lewat()->count());
    }

    public function test_relasi_antar_model(): void
    {
        $user = User::factory()->superAdmin()->create();
        $berita = Berita::factory()->create(['penulis_id' => $user->id]);
        $this->assertTrue($berita->penulis->is($user));

        $jabatan = Jabatan::factory()->create();
        $perangkat = PerangkatDesa::factory()->create(['jabatan_id' => $jabatan->id]);
        $this->assertTrue($jabatan->perangkat->first()->is($perangkat));

        $komoditas = Komoditas::factory()->create();
        $dusun = Dusun::factory()->create();
        $kelompok = KelompokTani::factory()->create(['komoditas_id' => $komoditas->id, 'dusun_id' => $dusun->id]);
        $this->assertTrue($kelompok->komoditas->is($komoditas));
        $this->assertTrue($kelompok->dusun->is($dusun));

        $aspirasi = Aspirasi::factory()->create();
        AspirasiRiwayat::factory()->create(['aspirasi_id' => $aspirasi->id, 'user_id' => $user->id]);
        $this->assertCount(1, $aspirasi->riwayat);
    }

    public function test_total_apbdes_dihitung_dari_rincian(): void
    {
        $periode = ApbdesPeriode::factory()->create();
        ApbdesRincian::factory()->create(['periode_id' => $periode->id, 'anggaran' => 1_000]);
        ApbdesRincian::factory()->create(['periode_id' => $periode->id, 'anggaran' => 2_500]);
        ApbdesRincian::factory()->create(['periode_id' => $periode->id, 'jenis' => JenisAnggaran::Belanja, 'anggaran' => 700]);
        ApbdesRincian::factory()->create(['periode_id' => $periode->id, 'jenis' => JenisAnggaran::Pembiayaan, 'anggaran' => null]);

        $periode = $periode->fresh();
        $this->assertSame(3_500, $periode->total(JenisAnggaran::Pendapatan));
        $this->assertSame(700, $periode->total(JenisAnggaran::Belanja));
        $this->assertNull($periode->total(JenisAnggaran::Pembiayaan));
    }

    public function test_nik_aspirasi_terenkripsi_di_database_dan_tersembunyi(): void
    {
        $aspirasi = Aspirasi::factory()->denganNik('1871010101010001')->create();

        $mentah = DB::table('aspirasi')->where('id', $aspirasi->id)->value('nik');
        $this->assertNotSame('1871010101010001', $mentah);
        $this->assertStringNotContainsString('1871010101010001', $mentah);
        $this->assertSame('1871010101010001', $aspirasi->fresh()->nik);
        $this->assertArrayNotHasKey('nik', $aspirasi->fresh()->toArray());
        $this->assertSame(StatusAspirasi::Baru, $aspirasi->fresh()->status);
    }

    public function test_peran_dan_penugasan_modul_user(): void
    {
        $super = User::factory()->superAdmin()->create();
        $staf = User::factory()->denganModul([Modul::Berita])->create();

        $this->assertSame(Peran::SuperAdmin, $super->peran);
        $this->assertTrue($super->punyaModul(Modul::Akun));
        $this->assertTrue($staf->punyaModul(Modul::Berita));
        $this->assertFalse($staf->punyaModul(Modul::Apbdes));
        $this->assertTrue(User::factory()->nonaktif()->create()->aktif === false);
    }

    public function test_kontak_hanya_tampil_bila_disetujui(): void
    {
        $this->assertNull(KelompokTani::factory()->create(['kontak' => '0812'])->kontakTampil());
        $this->assertSame('081200000000', KelompokTani::factory()->kontakDisetujui()->create()->kontakTampil());
        $this->assertNull(Umkm::factory()->create(['whatsapp' => '62812'])->whatsappTampil());
        $this->assertSame('6281200000000', Umkm::factory()->whatsappDisetujui()->create()->whatsappTampil());
    }

    public function test_slug_berita_unik(): void
    {
        Berita::factory()->create(['slug' => 'sama']);

        $this->expectException(UniqueConstraintViolationException::class);
        Berita::factory()->create(['slug' => 'sama']);
    }

    public function test_seeder_dengan_data_contoh_ditandai_dan_idempoten(): void
    {
        $this->seed(DataContohSeeder::class);
        $this->seed(DataContohSeeder::class);

        $this->assertSame(6, Dusun::count());
        $this->assertSame(8, Jabatan::count());
        $this->assertSame(1, ProfilDesa::count());
        $this->assertSame(5, Berita::count());
        $this->assertSame(4, Berita::terbit()->count());
        $this->assertSame(0, Berita::where('contoh', false)->count());
        $this->assertSame(3, ApbdesPeriode::count());
        $this->assertSame(11 * 3, ApbdesRincian::count());
        $this->assertSame(5, Aspirasi::count());
        $this->assertSame(3, Aspirasi::where('status', StatusAspirasi::Baru)->count());
        $this->assertStringContainsString('## Berbagi tugas', Berita::where('slug', 'gotong-royong-lingkungan-bersih')->value('isi'));
    }

    public function test_admin_awal_dibuat_dari_env_dengan_kata_sandi_acak(): void
    {
        config(['desa.admin.email' => 'admin@desa.test']);

        $this->seed(AdminAwalSeeder::class);
        $this->seed(AdminAwalSeeder::class);

        $admin = User::where('email', 'admin@desa.test')->sole();
        $this->assertSame(Peran::SuperAdmin, $admin->peran);
        $this->assertTrue($admin->aktif);
        $this->assertSame(1, User::count());
    }

    public function test_admin_awal_dilewati_bila_email_kosong(): void
    {
        config(['desa.admin.email' => null]);

        $this->seed(AdminAwalSeeder::class);

        $this->assertSame(0, User::count());
    }
}
