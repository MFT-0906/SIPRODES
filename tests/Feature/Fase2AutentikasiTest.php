<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class Fase2AutentikasiTest extends TestCase
{
    use RefreshDatabase;

    private const PESAN_GAGAL = 'Email/username atau kata sandi tidak sesuai. Silakan coba lagi.';

    public function test_tamu_diarahkan_ke_login_untuk_semua_rute_admin(): void
    {
        foreach (['/admin', '/admin/berita', '/admin/akun', '/admin/aspirasi', '/admin/apbdes', '/admin/profil'] as $path) {
            $this->get($path)->assertRedirect(route('admin.login'));
        }
        $this->post(route('admin.berita.store'), [])->assertRedirect(route('admin.login'));
        $this->post(route('admin.logout'))->assertRedirect(route('admin.login'));
    }

    public function test_login_berhasil_dengan_email_dan_mencatat_waktu_masuk(): void
    {
        $user = User::factory()->create(['email' => 'admin@desa.test']);

        $this->post(route('admin.login.store'), ['login' => 'admin@desa.test', 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->terakhir_masuk_at);
    }

    public function test_login_berhasil_dengan_username(): void
    {
        $user = User::factory()->create(['username' => 'bendahara']);

        $this->post(route('admin.login.store'), ['login' => 'bendahara', 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_kembali_ke_halaman_tujuan_semula(): void
    {
        User::factory()->create(['email' => 'a@desa.test']);

        $this->get('/admin/berita')->assertRedirect(route('admin.login'));
        $this->post(route('admin.login.store'), ['login' => 'a@desa.test', 'password' => 'password'])
            ->assertRedirect(route('admin.berita.index'));
    }

    public function test_login_salah_memakai_pesan_umum_untuk_akun_ada_maupun_tidak_ada(): void
    {
        User::factory()->create(['email' => 'ada@desa.test']);

        foreach ([['ada@desa.test', 'salah-banget'], ['tidak-ada@desa.test', 'password']] as [$login, $sandi]) {
            $this->from(route('admin.login'))
                ->post(route('admin.login.store'), ['login' => $login, 'password' => $sandi])
                ->assertRedirect(route('admin.login'))
                ->assertSessionHas('gagal_masuk', self::PESAN_GAGAL);
        }

        $this->assertGuest();
    }

    public function test_akun_nonaktif_ditolak_dengan_pesan_umum(): void
    {
        User::factory()->nonaktif()->create(['email' => 'off@desa.test']);

        $this->from(route('admin.login'))
            ->post(route('admin.login.store'), ['login' => 'off@desa.test', 'password' => 'password'])
            ->assertSessionHas('gagal_masuk', self::PESAN_GAGAL);

        $this->assertGuest();
    }

    public function test_akun_dinonaktifkan_saat_sesi_berjalan_langsung_dikeluarkan(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get(route('admin.dashboard'))->assertOk();

        $user->update(['aktif' => false]);

        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
        $this->assertGuest();
    }

    public function test_login_dikunci_setelah_gagal_berulang_walau_kata_sandi_benar(): void
    {
        User::factory()->create(['email' => 'kunci@desa.test']);

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('admin.login.store'), ['login' => 'kunci@desa.test', 'password' => 'salah'])
                ->assertSessionHas('gagal_masuk', self::PESAN_GAGAL);
        }

        $this->post(route('admin.login.store'), ['login' => 'kunci@desa.test', 'password' => 'password'])
            ->assertSessionHas('gagal_masuk', fn (string $pesan) => str_contains($pesan, 'Terlalu banyak percobaan'));
        $this->assertGuest();
    }

    public function test_login_berhasil_mereset_hitungan_gagal(): void
    {
        User::factory()->create(['email' => 'reset@desa.test']);

        for ($i = 0; $i < 4; $i++) {
            $this->post(route('admin.login.store'), ['login' => 'reset@desa.test', 'password' => 'salah']);
        }
        $this->post(route('admin.login.store'), ['login' => 'reset@desa.test', 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));
        $this->post(route('admin.logout'));

        $this->post(route('admin.login.store'), ['login' => 'reset@desa.test', 'password' => 'salah'])
            ->assertSessionHas('gagal_masuk', self::PESAN_GAGAL);
    }

    public function test_pengguna_yang_sudah_masuk_tidak_melihat_halaman_login(): void
    {
        $this->actingAs(User::factory()->create())->get(route('admin.login'))->assertRedirect(route('admin.dashboard'));
    }

    public function test_logout_mengakhiri_sesi(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('admin.logout'))->assertRedirect(route('admin.login'));

        $this->assertGuest();
        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
    }

    public function test_lupa_kata_sandi_mengirim_tautan_hanya_untuk_akun_aktif_dengan_respons_sama(): void
    {
        Notification::fake();
        $aktif = User::factory()->create(['email' => 'aktif@desa.test']);
        $nonaktif = User::factory()->nonaktif()->create(['email' => 'off@desa.test']);

        foreach (['aktif@desa.test', 'off@desa.test', 'tidak-ada@desa.test'] as $email) {
            $this->from(route('admin.password.request'))
                ->post(route('admin.password.email'), ['email' => $email])
                ->assertRedirect(route('admin.password.request'))
                ->assertSessionHas('tautan_terkirim', true);
        }

        Notification::assertSentTo($aktif, ResetPassword::class);
        Notification::assertNotSentTo($nonaktif, ResetPassword::class);
        Notification::assertCount(1);
    }

    public function test_lupa_kata_sandi_memvalidasi_format_email(): void
    {
        $this->from(route('admin.password.request'))
            ->post(route('admin.password.email'), ['email' => 'bukan-email'])
            ->assertSessionHasErrors(['email' => 'Format email belum benar.']);
    }

    public function test_email_atur_ulang_berbahasa_indonesia_dan_menunjuk_ke_halaman_admin(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $this->post(route('admin.password.email'), ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $n) use ($user) {
            $mail = $n->toMail($user);

            return str_starts_with($mail->actionUrl, url('/admin/atur-ulang-kata-sandi/'))
                && str_contains($mail->subject, 'Atur ulang kata sandi')
                && $mail->actionText === 'Buat kata sandi baru';
        });
    }

    public function test_alur_atur_ulang_kata_sandi_berhasil(): void
    {
        $user = User::factory()->create(['email' => 'lupa@desa.test']);
        $token = Password::createToken($user);

        $this->get(route('admin.password.reset', ['token' => $token, 'email' => $user->email]))
            ->assertOk()->assertSee('Buat kata sandi baru');

        $this->post(route('admin.password.update'), [
            'token' => $token, 'email' => $user->email,
            'password' => 'KataSandiBaru123', 'password_confirmation' => 'KataSandiBaru123',
        ])->assertRedirect(route('admin.login'))->assertSessionHas('status_masuk');

        $this->post(route('admin.login.store'), ['login' => $user->email, 'password' => 'KataSandiBaru123'])
            ->assertRedirect(route('admin.dashboard'));

        // Token sekali pakai.
        $this->post(route('admin.logout'));
        $this->post(route('admin.password.update'), [
            'token' => $token, 'email' => $user->email,
            'password' => 'LainnyaLagi12345', 'password_confirmation' => 'LainnyaLagi12345',
        ])->assertSessionHasErrors('email');
    }

    public function test_atur_ulang_menolak_token_salah_dan_akun_nonaktif(): void
    {
        $user = User::factory()->create();
        $off = User::factory()->nonaktif()->create();

        $this->post(route('admin.password.update'), [
            'token' => 'salah', 'email' => $user->email,
            'password' => 'KataSandiBaru123', 'password_confirmation' => 'KataSandiBaru123',
        ])->assertSessionHasErrors('email');

        $this->post(route('admin.password.update'), [
            'token' => Password::createToken($off), 'email' => $off->email,
            'password' => 'KataSandiBaru123', 'password_confirmation' => 'KataSandiBaru123',
        ])->assertSessionHasErrors('email');
    }

    public function test_kebijakan_kata_sandi_minimal_10_dan_tidak_sama_dengan_email(): void
    {
        $user = User::factory()->create(['email' => 'panjang-email@desa.test']);
        $token = Password::createToken($user);
        $kirim = fn (string $sandi, ?string $konfirmasi = null) => $this->post(route('admin.password.update'), [
            'token' => $token, 'email' => $user->email, 'password' => $sandi, 'password_confirmation' => $konfirmasi ?? $sandi,
        ]);

        $kirim('pendek123')->assertSessionHasErrors(['password' => 'Kata sandi minimal 10 karakter.']);
        $kirim('panjang-email@desa.test')->assertSessionHasErrors(['password' => 'Kata sandi tidak boleh sama dengan email.']);
        $kirim('KataSandiBaru123', 'Berbeda1234567')->assertSessionHasErrors(['password' => 'Konfirmasi kata sandi tidak sama.']);
        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }
}
