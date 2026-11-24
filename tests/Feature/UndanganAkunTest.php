<?php

namespace Tests\Feature;

use App\Models\LogAktivitas;
use App\Models\User;
use App\Notifications\UndanganAkun;
use App\Support\Aktivitas;
use App\Support\TautanUndangan;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

/**
 * Email undangan untuk akun admin baru: isi email, masa berlaku 72 jam (undangan) vs 60 menit (lupa kata sandi),
 * pemilihan jenis email pada tombol kirim ulang, dan log aktivitas tanpa token.
 */
class UndanganAkunTest extends TestCase
{
    use RefreshDatabase;

    private function masukSuperAdmin(): User
    {
        $admin = User::factory()->superAdmin()->create();
        $this->actingAs($admin);

        return $admin;
    }

    /**
     * @return array{0: User, 1: string}
     */
    private function buatAkunDanAmbilToken(): array
    {
        Notification::fake();
        $this->masukSuperAdmin();
        $this->post(route('admin.akun.store'), ['nama' => 'Sri Wahyuni', 'email' => 'sri@desa.test', 'peran' => 'staf', 'modul' => ['berita']])
            ->assertRedirect(route('admin.akun.index'));

        $user = User::where('email', 'sri@desa.test')->sole();
        // Pengguna baru membuka tautan sebagai tamu, bukan sebagai Super Admin yang membuat akun.
        auth()->forgetGuards();
        $this->app['session']->flush();
        $token = '';
        Notification::assertSentTo($user, UndanganAkun::class, function (UndanganAkun $n) use (&$token) {
            $token = $n->token;

            return true;
        });

        return [$user, $token];
    }

    /**
     * @return array<string, string>
     */
    private function isiAtur(User $user, string $token, ?string $tanda = null): array
    {
        return array_filter([
            'token' => $token, 'email' => $user->email, 'tanda' => $tanda,
            'password' => 'KataSandiBaru123', 'password_confirmation' => 'KataSandiBaru123',
        ]);
    }

    public function test_membuat_akun_mengirim_undangan_bukan_email_atur_ulang(): void
    {
        [$user] = $this->buatAkunDanAmbilToken();

        Notification::assertSentTo($user, UndanganAkun::class);
        Notification::assertNotSentTo($user, ResetPassword::class);
    }

    public function test_isi_email_memuat_nama_peran_tombol_masa_berlaku_dan_tanpa_kata_sandi(): void
    {
        [$user, $token] = $this->buatAkunDanAmbilToken();

        $mail = (new UndanganAkun($token))->toMail($user);
        $teks = implode("\n", [$mail->subject, $mail->greeting, ...$mail->introLines, ...$mail->outroLines]);

        $this->assertSame('Akun Anda di Website Desa Marga Agung', $mail->subject);
        $this->assertSame('Halo Sri Wahyuni,', $mail->greeting);
        $this->assertStringContainsString('Super Admin telah membuatkan akun admin untuk Anda dengan peran Staf.', $teks);
        $this->assertSame('Buat kata sandi', $mail->actionText);
        $this->assertStringContainsString('berlaku selama 72 jam', $teks);
        $this->assertStringContainsString('Masuk dengan Google', $teks);
        $this->assertStringStartsWith(url('/admin/atur-ulang-kata-sandi/'.$token), $mail->actionUrl);
        $this->assertStringNotContainsString('KataSandi', $teks);
    }

    public function test_tautan_undangan_masih_berlaku_setelah_2_jam_dan_kedaluwarsa_setelah_72_jam(): void
    {
        [$user, $token] = $this->buatAkunDanAmbilToken();
        $tanda = TautanUndangan::tanda($user->email, $token);

        $this->travel(2)->hours();
        $this->get(route('admin.password.reset', ['token' => $token, 'email' => $user->email, 'tanda' => $tanda]))
            ->assertOk()->assertSee('Buat kata sandi')->assertDontSee('Buat kata sandi baru');

        $this->travel(70)->hours();
        $this->travel(1)->minutes();
        $this->post(route('admin.password.update'), $this->isiAtur($user, $token, $tanda))->assertSessionHasErrors('email');
    }

    public function test_tautan_undangan_berhasil_dipakai_dalam_72_jam(): void
    {
        [$user, $token] = $this->buatAkunDanAmbilToken();

        $this->travel(2)->hours();
        $this->post(route('admin.password.update'), $this->isiAtur($user, $token, TautanUndangan::tanda($user->email, $token)))
            ->assertRedirect(route('admin.login'))->assertSessionHas('status_masuk');
    }

    public function test_tautan_lupa_kata_sandi_tetap_kedaluwarsa_setelah_60_menit(): void
    {
        $user = User::factory()->create(['email' => 'lupa@desa.test']);
        $token = Password::createToken($user);

        $this->travel(61)->minutes();
        $this->post(route('admin.password.update'), $this->isiAtur($user, $token))->assertSessionHasErrors('email');
    }

    public function test_tanda_palsu_tidak_memperpanjang_tautan_lupa_kata_sandi(): void
    {
        $user = User::factory()->create(['email' => 'lupa@desa.test']);
        $token = Password::createToken($user);

        $this->travel(2)->hours();
        foreach (['palsu', TautanUndangan::tanda($user->email, 'token-lain')] as $tanda) {
            $this->post(route('admin.password.update'), $this->isiAtur($user, $token, $tanda))->assertSessionHasErrors('email');
        }
        $this->get(route('admin.password.reset', ['token' => $token, 'email' => $user->email, 'tanda' => 'palsu']))
            ->assertOk()->assertSee('Buat kata sandi baru');
    }

    public function test_tombol_kirim_ulang_memilih_undangan_bila_belum_pernah_masuk_dan_atur_ulang_bila_sudah(): void
    {
        Notification::fake();
        $this->masukSuperAdmin();
        $belum = User::factory()->create(['terakhir_masuk_at' => null]);
        $sudah = User::factory()->create(['terakhir_masuk_at' => now()->subDay()]);

        $this->post(route('admin.akun.reset', $belum))->assertSessionHas('toast', 'Undangan dikirim ulang. Pengguna menerima email untuk membuat kata sandi.');
        $this->post(route('admin.akun.reset', $sudah))->assertSessionHas('toast', 'Tautan atur ulang kata sandi dikirim ke pengguna.');

        Notification::assertSentTo($belum, UndanganAkun::class);
        Notification::assertNotSentTo($belum, ResetPassword::class);
        Notification::assertSentTo($sudah, ResetPassword::class);
        Notification::assertNotSentTo($sudah, UndanganAkun::class);

        $this->get(route('admin.akun.index'))->assertSee('Kirim ulang undangan akun: '.$belum->name)
            ->assertSee('Kirim tautan atur ulang kata sandi: '.$sudah->name);
    }

    public function test_akun_nonaktif_tidak_dikirimi_undangan(): void
    {
        Notification::fake();
        $this->masukSuperAdmin();
        $off = User::factory()->nonaktif()->create(['terakhir_masuk_at' => null]);

        $this->post(route('admin.akun.reset', $off))->assertRedirect();

        Notification::assertNothingSentTo($off);
    }

    public function test_pengiriman_undangan_dicatat_di_log_tanpa_token(): void
    {
        [$user, $token] = $this->buatAkunDanAmbilToken();
        $this->travel(2)->minutes();
        $this->masukSuperAdmin();
        $this->post(route('admin.akun.reset', $user));

        $log = LogAktivitas::where('aksi', 'akun.undangan_dikirim')->get();
        $this->assertCount(2, $log);
        $this->assertSame('sri@desa.test', $log->first()->keterangan);
        $semua = json_encode(LogAktivitas::all()->toArray());
        $this->assertStringNotContainsString($token, $semua);
        $this->assertStringNotContainsString('tanda=', $semua);
        $this->assertSame('Undangan akun dikirim', Aktivitas::LABEL['akun.undangan_dikirim']);
    }
}
