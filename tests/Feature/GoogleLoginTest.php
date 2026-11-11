<?php

namespace Tests\Feature;

use App\Models\LogAktivitas;
use App\Models\User;
use App\Support\GoogleLogin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * "Masuk dengan Google": hanya akun terdaftar dan aktif, akun Google tertaut sekali, dan aman dari pemalsuan state.
 * Semua panggilan ke Google ditiru (Http::fake); tes tidak membutuhkan internet.
 */
class GoogleLoginTest extends TestCase
{
    use RefreshDatabase;

    private function aktifkan(): void
    {
        config(['desa.google.client_id' => 'klien-uji.apps.googleusercontent.com', 'desa.google.client_secret' => 'rahasia-uji']);
    }

    /** @param  array<string, mixed>  $profil */
    private function googleMenjawab(array $profil = []): void
    {
        Http::fake([
            'oauth2.googleapis.com/token' => Http::response(['access_token' => 'akses-uji', 'token_type' => 'Bearer', 'expires_in' => 3599]),
            'openidconnect.googleapis.com/*' => Http::response($profil + [
                'sub' => '1122334455', 'email' => 'sekdes@gmail.com', 'email_verified' => true, 'name' => 'Sekdes Uji',
            ]),
        ]);
    }

    /** Memulai alur seperti peramban: klik tombol, lalu kembali dari Google dengan state yang sama. */
    private function kembaliDariGoogle(array $tambahan = []): TestResponse
    {
        $arah = $this->get(route('admin.login.google'));
        parse_str((string) parse_url($arah->headers->get('Location'), PHP_URL_QUERY), $q);

        return $this->get(route('admin.login.google.kembali', $tambahan + ['state' => $q['state'], 'code' => 'kode-uji']));
    }

    private function aksi(): array
    {
        return LogAktivitas::query()->orderBy('id')->pluck('aksi')->all();
    }

    public function test_tanpa_kunci_tombol_tidak_tampil_dan_rute_404(): void
    {
        $this->get(route('admin.login'))->assertOk()->assertDontSee('Masuk dengan Google');
        $this->get(route('admin.login.google'))->assertNotFound();
        $this->get(route('admin.login.google.kembali'))->assertNotFound();
    }

    public function test_logo_resmi_google_dipakai_bila_berkasnya_ada(): void
    {
        $this->aktifkan();
        $this->withoutVite();

        // Folder public sementara: mula-mula tanpa logo, lalu dengan logo.
        $folder = sys_get_temp_dir().'/siprodes-publik-'.uniqid();
        mkdir($folder.'/images', 0777, true);
        $this->app->usePublicPath($folder);

        try {
            $this->get(route('admin.login'))->assertSee('Masuk dengan Google')->assertDontSee('google-g.svg');

            file_put_contents($folder.'/images/google-g.svg', '<svg xmlns="http://www.w3.org/2000/svg"/>');
            $this->get(route('admin.login'))->assertSee('images/google-g.svg', false);
        } finally {
            @unlink($folder.'/images/google-g.svg');
            rmdir($folder.'/images');
            rmdir($folder);
        }
    }

    public function test_tombol_tampil_dan_mengarahkan_ke_google_dengan_pkce(): void
    {
        $this->aktifkan();
        $this->get(route('admin.login'))->assertOk()->assertSee('Masuk dengan Google')->assertDontSee('rahasia-uji');

        $respons = $this->get(route('admin.login.google'))->assertRedirect();
        $lokasi = $respons->headers->get('Location');
        $this->assertStringStartsWith('https://accounts.google.com/o/oauth2/v2/auth?', $lokasi);
        parse_str((string) parse_url($lokasi, PHP_URL_QUERY), $q);

        $this->assertSame('klien-uji.apps.googleusercontent.com', $q['client_id']);
        $this->assertSame(route('admin.login.google.kembali'), $q['redirect_uri']);
        $this->assertSame('openid email profile', $q['scope']);
        $this->assertSame('S256', $q['code_challenge_method']);
        $this->assertSame(GoogleLogin::tantangan(session('google_oauth.verifier')), $q['code_challenge']);
        $this->assertSame(session('google_oauth.state'), $q['state']);
        $this->assertArrayNotHasKey('client_secret', $q);
    }

    public function test_akun_terdaftar_masuk_dan_akun_google_ditautkan(): void
    {
        $this->aktifkan();
        $this->googleMenjawab(['email' => 'Sekdes@Gmail.com']);
        $user = User::factory()->create(['email' => 'sekdes@gmail.com']);

        $this->kembaliDariGoogle()->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->assertSame('1122334455', $user->refresh()->google_id);
        $this->assertNotNull($user->terakhir_masuk_at);
        $this->assertSame(['masuk.google'], $this->aksi());

        Http::assertSent(fn (Request $r) => str_contains($r->url(), 'oauth2.googleapis.com/token')
            && $r['code'] === 'kode-uji' && $r['grant_type'] === 'authorization_code'
            && $r['redirect_uri'] === route('admin.login.google.kembali') && strlen($r['code_verifier']) === 64);
    }

    public function test_email_tidak_terdaftar_ditolak_dan_dicatat(): void
    {
        $this->aktifkan();
        $this->googleMenjawab(['email' => 'orang.luar@gmail.com']);
        User::factory()->create(['email' => 'sekdes@gmail.com']);

        $this->kembaliDariGoogle()->assertRedirect(route('admin.login'))->assertSessionHas('gagal_masuk');

        $this->assertGuest();
        $this->assertSame(['masuk.google_ditolak'], $this->aksi());
        $this->assertStringContainsString('orang.luar@gmail.com', LogAktivitas::value('keterangan'));
    }

    public function test_akun_nonaktif_ditolak(): void
    {
        $this->aktifkan();
        $this->googleMenjawab();
        User::factory()->nonaktif()->create(['email' => 'sekdes@gmail.com']);

        $this->kembaliDariGoogle()->assertRedirect(route('admin.login'));
        $this->assertGuest();
    }

    public function test_email_google_belum_terverifikasi_ditolak(): void
    {
        $this->aktifkan();
        $this->googleMenjawab(['email_verified' => false]);
        User::factory()->create(['email' => 'sekdes@gmail.com']);

        $this->kembaliDariGoogle()->assertRedirect(route('admin.login'));
        $this->assertGuest();
    }

    public function test_akun_google_lain_dengan_email_sama_ditolak_setelah_tertaut(): void
    {
        $this->aktifkan();
        $this->googleMenjawab(['sub' => '9999999999']);
        $user = User::factory()->create(['email' => 'sekdes@gmail.com']);
        $user->forceFill(['google_id' => '1122334455'])->save();

        $this->kembaliDariGoogle()->assertRedirect(route('admin.login'))->assertSessionHas('gagal_masuk');
        $this->assertGuest();
        $this->assertSame('1122334455', $user->refresh()->google_id);
    }

    public function test_satu_akun_google_tidak_bisa_tertaut_ke_dua_akun_admin(): void
    {
        $this->aktifkan();
        $this->googleMenjawab();
        $lain = User::factory()->create(['email' => 'lama@gmail.com']);
        $lain->forceFill(['google_id' => '1122334455'])->save();
        User::factory()->create(['email' => 'sekdes@gmail.com']);

        $this->kembaliDariGoogle()->assertRedirect(route('admin.login'));
        $this->assertGuest();
    }

    public function test_state_palsu_atau_tanpa_sesi_ditolak_tanpa_menghubungi_google(): void
    {
        $this->aktifkan();
        Http::fake();
        User::factory()->create(['email' => 'sekdes@gmail.com']);

        $this->get(route('admin.login.google.kembali', ['state' => 'palsu', 'code' => 'kode-uji']))
            ->assertRedirect(route('admin.login'))->assertSessionHas('gagal_masuk');

        $this->get(route('admin.login.google'));
        $this->get(route('admin.login.google.kembali', ['state' => 'palsu', 'code' => 'kode-uji']))->assertRedirect(route('admin.login'));

        $this->assertGuest();
        Http::assertNothingSent();
    }

    public function test_state_hanya_sekali_pakai(): void
    {
        $this->aktifkan();
        $this->googleMenjawab();
        User::factory()->create(['email' => 'sekdes@gmail.com']);

        $arah = $this->get(route('admin.login.google'));
        parse_str((string) parse_url($arah->headers->get('Location'), PHP_URL_QUERY), $q);
        $this->get(route('admin.login.google.kembali', ['state' => $q['state'], 'code' => 'kode-uji']))->assertRedirect(route('admin.dashboard'));

        $this->post(route('admin.logout'));
        $this->get(route('admin.login.google.kembali', ['state' => $q['state'], 'code' => 'kode-uji']))->assertRedirect(route('admin.login'));
        $this->assertGuest();
    }

    public function test_pengguna_membatalkan_di_google(): void
    {
        $this->aktifkan();
        Http::fake();

        $this->kembaliDariGoogle(['error' => 'access_denied'])
            ->assertRedirect(route('admin.login'))->assertSessionHas('gagal_masuk', 'Masuk dengan Google dibatalkan.');
        Http::assertNothingSent();
    }

    public function test_google_gagal_menukar_kode(): void
    {
        $this->aktifkan();
        Http::fake(['oauth2.googleapis.com/token' => Http::response(['error' => 'invalid_grant'], 400)]);
        User::factory()->create(['email' => 'sekdes@gmail.com']);

        $this->kembaliDariGoogle()->assertRedirect(route('admin.login'))->assertSessionHas('gagal_masuk');
        $this->assertGuest();
    }

    public function test_super_admin_melepas_tautan_dan_ganti_email_juga_melepas(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $staf = User::factory()->create(['email' => 'sekdes@gmail.com']);
        $staf->forceFill(['google_id' => '1122334455'])->save();

        $data = ['nama' => $staf->name, 'email' => 'sekdes@gmail.com', 'peran' => 'staf', 'aktif' => '1', '_mode' => 'ubah'];

        $this->actingAs($admin)->get(route('admin.akun.index', ['ubah' => $staf->id]))->assertSee('Lepas tautan akun Google');

        $this->actingAs($admin)->put(route('admin.akun.update', $staf), $data)->assertRedirect(route('admin.akun.index'));
        $this->assertSame('1122334455', $staf->refresh()->google_id);

        $this->actingAs($admin)->put(route('admin.akun.update', $staf), $data + ['lepas_google' => '1']);
        $this->assertNull($staf->refresh()->google_id);
        $this->assertContains('akun.google_dilepas', $this->aksi());

        $staf->forceFill(['google_id' => '1122334455'])->save();
        $this->actingAs($admin)->put(route('admin.akun.update', $staf), ['email' => 'sekdes.baru@gmail.com'] + $data);
        $this->assertNull($staf->refresh()->google_id);
    }
}
