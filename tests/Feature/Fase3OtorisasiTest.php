<?php

namespace Tests\Feature;

use App\Enums\Modul;
use App\Enums\Peran;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class Fase3OtorisasiTest extends TestCase
{
    use RefreshDatabase;

    /** @var array<string, list<string>> modul => rute GET bernama */
    private const RUTE_PER_MODUL = [
        'berita' => ['admin.berita.index', 'admin.agenda.index'],
        'potensi' => ['admin.pertanian', 'admin.kelompok-tani', 'admin.umkm'],
        'profil' => ['admin.profil', 'admin.struktur'],
        'apbdes' => ['admin.apbdes'],
        'aspirasi' => ['admin.aspirasi.index'],
        'akun' => ['admin.akun.index'],
    ];

    private function superAdmin(): User
    {
        return User::factory()->superAdmin()->create();
    }

    public function test_staf_tanpa_penugasan_mendapat_403_di_setiap_modul(): void
    {
        $this->actingAs(User::factory()->create());

        foreach (self::RUTE_PER_MODUL as $rute) {
            foreach ($rute as $nama) {
                $this->get(route($nama))->assertForbidden();
            }
        }

        $this->post(route('admin.berita.store'), [])->assertForbidden();
        $this->delete(route('admin.umkm.destroy', 1))->assertForbidden();
        $this->get(route('admin.dashboard'))->assertOk();
    }

    public function test_staf_dengan_penugasan_hanya_membuka_modulnya(): void
    {
        $this->actingAs(User::factory()->denganModul([Modul::Berita, Modul::Aspirasi])->create());

        foreach (['berita', 'aspirasi'] as $modul) {
            foreach (self::RUTE_PER_MODUL[$modul] as $nama) {
                $this->get(route($nama))->assertOk();
            }
        }
        foreach (['potensi', 'profil', 'apbdes', 'akun'] as $modul) {
            foreach (self::RUTE_PER_MODUL[$modul] as $nama) {
                $this->get(route($nama))->assertForbidden();
            }
        }
    }

    public function test_super_admin_membuka_semua_modul(): void
    {
        $this->actingAs($this->superAdmin());

        foreach (self::RUTE_PER_MODUL as $rute) {
            foreach ($rute as $nama) {
                $this->get(route($nama))->assertOk();
            }
        }
    }

    public function test_modul_akun_tetap_tertutup_untuk_staf_walau_ada_baris_penugasan(): void
    {
        $staf = User::factory()->create();
        $staf->modulAkses()->create(['modul' => Modul::Akun]);

        $this->actingAs($staf)->get(route('admin.akun.index'))->assertForbidden();
        $this->post(route('admin.akun.store'), ['nama' => 'X', 'email' => 'x@desa.test', 'peran' => 'super_admin'])->assertForbidden();
        $this->assertSame(1, User::count());
    }

    public function test_menu_sidebar_mengikuti_penugasan(): void
    {
        $this->actingAs(User::factory()->denganModul([Modul::Berita])->create(['name' => 'Staf Berita']));

        $this->get(route('admin.dashboard'))->assertOk()
            ->assertSee(route('admin.berita.index'), false)
            ->assertSee(route('admin.agenda.index'), false)
            ->assertDontSee(route('admin.apbdes'), false)
            ->assertDontSee(route('admin.aspirasi.index'), false)
            ->assertDontSee('Aspirasi Terbaru')
            ->assertDontSee('Aspirasi belum diproses')
            ->assertDontSee('Unggah dokumen APBDes')
            ->assertSee('Tambah berita')
            ->assertDontSee('Akun &amp; Hak Akses', false)
            ->assertSee('Staf Berita');
    }

    public function test_menu_akun_tampil_untuk_super_admin(): void
    {
        $this->actingAs($this->superAdmin())->get(route('admin.dashboard'))->assertOk()
            ->assertSee('Akun &amp; Hak Akses', false)->assertSee('Super Admin');
    }

    public function test_parameter_peran_tidak_lagi_berpengaruh(): void
    {
        $this->actingAs(User::factory()->create());
        $this->app->detectEnvironment(fn () => 'local');

        $this->get(route('admin.akun.index', ['peran' => 'super_admin']))->assertForbidden();
        $this->get(route('admin.dashboard', ['peran' => 'super_admin']))->assertOk()
            ->assertDontSee('Akun &amp; Hak Akses', false);
    }

    public function test_halaman_akun_menampilkan_matriks_dari_modul_dan_tanpa_kolom_kata_sandi(): void
    {
        $this->actingAs($this->superAdmin())->get(route('admin.akun.index'))->assertOk()
            ->assertSee('Matriks Izin')->assertSee('Sesuai penugasan')
            ->assertSee(Modul::Apbdes->label())
            ->assertSee('Kata sandi tidak diisi admin')->assertDontSee('name="password"', false);
    }

    public function test_tambah_akun_tanpa_kata_sandi_mengirim_tautan_dan_menyimpan_modul(): void
    {
        Notification::fake();
        $this->actingAs($this->superAdmin());

        $this->post(route('admin.akun.store'), [
            'nama' => 'Staf Baru', 'email' => 'baru@desa.test', 'username' => 'staf.baru',
            'peran' => 'staf', 'modul' => ['berita', 'potensi'],
        ])->assertRedirect(route('admin.akun.index'))->assertSessionHas('toast');

        $baru = User::where('email', 'baru@desa.test')->sole();
        $this->assertSame(Peran::Staf, $baru->peran);
        $this->assertTrue($baru->aktif);
        $this->assertSame('staf.baru', $baru->username);
        $this->assertEqualsCanonicalizing(['berita', 'potensi'], $baru->modulAkses->map(fn ($m) => $m->modul->value)->all());
        Notification::assertSentTo($baru, ResetPassword::class);
    }

    public function test_super_admin_baru_tidak_menyimpan_penugasan_modul(): void
    {
        Notification::fake();
        $this->actingAs($this->superAdmin());

        $this->post(route('admin.akun.store'), [
            'nama' => 'Admin Dua', 'email' => 'dua@desa.test', 'peran' => 'super_admin', 'modul' => ['berita'],
        ])->assertRedirect();

        $this->assertSame(0, User::where('email', 'dua@desa.test')->sole()->modulAkses()->count());
    }

    public function test_validasi_akun_email_wajib_unik_dan_modul_akun_tidak_dapat_ditugaskan(): void
    {
        User::factory()->create(['email' => 'ada@desa.test', 'username' => 'dipakai']);
        $this->actingAs($this->superAdmin());

        $this->from(route('admin.akun.index'))->post(route('admin.akun.store'), ['peran' => 'dewa'])
            ->assertSessionHasErrors(['nama', 'email', 'peran']);

        $this->from(route('admin.akun.index'))->post(route('admin.akun.store'), [
            'nama' => 'X', 'email' => 'ada@desa.test', 'username' => 'dipakai', 'peran' => 'staf', 'modul' => ['akun'],
        ])->assertSessionHasErrors(['email', 'username', 'modul.0']);

        $this->from(route('admin.akun.index'))->post(route('admin.akun.store'), [
            'nama' => 'X', 'email' => 'bukan-email', 'username' => 'a@b', 'peran' => 'staf',
        ])->assertSessionHasErrors(['email', 'username']);
    }

    public function test_ubah_akun_peran_modul_dan_status(): void
    {
        $this->actingAs($this->superAdmin());
        $staf = User::factory()->denganModul([Modul::Berita])->create(['email' => 'lama@desa.test']);

        $this->put(route('admin.akun.update', $staf), [
            'nama' => 'Nama Baru', 'email' => 'lama@desa.test', 'peran' => 'staf', 'modul' => ['apbdes'], 'aktif' => '1',
        ])->assertRedirect(route('admin.akun.index'));

        $staf->refresh()->load('modulAkses');
        $this->assertSame('Nama Baru', $staf->name);
        $this->assertSame(['apbdes'], $staf->modulAkses->map(fn ($m) => $m->modul->value)->all());

        $this->put(route('admin.akun.update', $staf), [
            'nama' => 'Nama Baru', 'email' => 'lama@desa.test', 'peran' => 'staf',
        ])->assertRedirect();
        $this->assertFalse($staf->fresh()->aktif);
        $this->assertSame(0, $staf->modulAkses()->count());

        $this->get(route('admin.akun.index', ['ubah' => $staf->id]))->assertOk()->assertSee('Ubah Akun Admin');
        $this->put(route('admin.akun.update', 99999), [])->assertNotFound();
    }

    public function test_tidak_boleh_menonaktifkan_akun_sendiri(): void
    {
        $saya = $this->superAdmin();
        $this->superAdmin();
        $this->actingAs($saya);

        $this->from(route('admin.akun.index'))->put(route('admin.akun.update', $saya), [
            'nama' => $saya->name, 'email' => $saya->email, 'peran' => 'super_admin',
        ])->assertSessionHasErrors('aktif');

        $this->assertTrue($saya->fresh()->aktif);
    }

    public function test_super_admin_terakhir_tidak_bisa_dinonaktifkan_atau_diturunkan(): void
    {
        $terakhir = $this->superAdmin();
        $staf = User::factory()->create();
        $this->actingAs($terakhir);

        // Menurunkan peran sendiri saat satu-satunya Super Admin aktif.
        $this->from(route('admin.akun.index'))->put(route('admin.akun.update', $terakhir), [
            'nama' => 'A', 'email' => $terakhir->email, 'peran' => 'staf', 'aktif' => '1',
        ])->assertSessionHasErrors('peran');
        $this->assertSame(Peran::SuperAdmin, $terakhir->fresh()->peran);

        // Super Admin kedua boleh diturunkan selama masih ada yang lain; setelah itu terakhir terlindungi.
        $kedua = $this->superAdmin();
        $this->put(route('admin.akun.update', $kedua), [
            'nama' => 'B', 'email' => $kedua->email, 'peran' => 'staf', 'aktif' => '1',
        ])->assertRedirect();
        $this->assertSame(Peran::Staf, $kedua->fresh()->peran);

        $this->actingAs($this->superAdmin());
        $this->put(route('admin.akun.update', $terakhir), [
            'nama' => 'A', 'email' => $terakhir->email, 'peran' => 'super_admin',
        ])->assertRedirect();
        $this->assertFalse($terakhir->fresh()->aktif);
        $this->assertNotNull($staf->fresh());
    }

    public function test_kirim_tautan_atur_ulang_hanya_untuk_akun_aktif(): void
    {
        Notification::fake();
        $this->actingAs($this->superAdmin());
        $aktif = User::factory()->create();
        $off = User::factory()->nonaktif()->create();

        $this->post(route('admin.akun.reset', $aktif))->assertRedirect()->assertSessionHas('toast', 'Tautan atur ulang kata sandi dikirim ke pengguna.');
        $this->post(route('admin.akun.reset', $off))->assertRedirect();

        Notification::assertSentTo($aktif, ResetPassword::class);
        Notification::assertNotSentTo($off, ResetPassword::class);
    }

    public function test_gate_akses_modul_bagi_akun_nonaktif_ditolak(): void
    {
        $this->assertFalse(Gate::forUser(User::factory()->nonaktif()->superAdmin()->make())->allows('akses-modul', Modul::Berita));
        $this->assertTrue(Gate::forUser(User::factory()->superAdmin()->make())->allows('akses-modul', Modul::Berita));
    }
}
