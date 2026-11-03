<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\AturUlangKataSandiRequest;
use App\Http\Requests\Admin\LupaKataSandiRequest;
use App\Http\Requests\Admin\MasukRequest;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AdminAuthController extends Controller
{
    private const PESAN_GAGAL = 'Email/username atau kata sandi tidak sesuai. Silakan coba lagi.';

    public function login(): View
    {
        return view('pages.admin.login');
    }

    public function masuk(MasukRequest $request): RedirectResponse
    {
        $kunci = $this->kunciPercobaan($request);
        $maks = (int) config('desa.auth.percobaan_maks');

        // Penguncian berlaku per (login + IP) dan tidak bergantung pada ada-tidaknya akun.
        if (RateLimiter::tooManyAttempts($kunci, $maks)) {
            $menit = (int) ceil(RateLimiter::availableIn($kunci) / 60);

            return back()
                ->withInput($request->only('login'))
                ->with('gagal_masuk', "Terlalu banyak percobaan masuk. Coba lagi dalam {$menit} menit.");
        }

        $login = trim($request->string('login')->toString());
        $kolom = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $berhasil = Auth::attempt(
            [$kolom => $login, 'password' => $request->string('password')->toString(), 'aktif' => true],
            $request->boolean('ingat'),
        );

        if (! $berhasil) {
            RateLimiter::hit($kunci, (int) config('desa.auth.kunci_menit') * 60);

            // Pesan umum: tidak membocorkan akun mana yang ada atau nonaktif.
            return back()->withInput($request->only('login'))->with('gagal_masuk', self::PESAN_GAGAL);
        }

        RateLimiter::clear($kunci);
        $request->session()->regenerate();
        $request->user()->forceFill(['terakhir_masuk_at' => now()])->save();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function lupa(): View
    {
        return view('pages.admin.lupa-kata-sandi');
    }

    public function kirimTautan(LupaKataSandiRequest $request): RedirectResponse
    {
        $email = $request->string('email')->toString();

        // Hanya akun aktif yang dikirimi tautan; respons selalu sama agar email terdaftar tidak terbongkar.
        if (User::query()->where('email', $email)->where('aktif', true)->exists()) {
            Password::sendResetLink(['email' => $email]);
        }

        return back()->with('tautan_terkirim', true);
    }

    public function formAturUlang(Request $request, string $token): View
    {
        return view('pages.admin.atur-ulang-kata-sandi', [
            'token' => $token,
            'email' => (string) $request->query('email', ''),
        ]);
    }

    public function aturUlang(AturUlangKataSandiRequest $request): RedirectResponse
    {
        $email = $request->string('email')->toString();
        $akunAktif = User::query()->where('email', $email)->where('aktif', true)->exists();

        $status = $akunAktif
            ? Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function (User $user, string $kataSandi): void {
                    $user->forceFill([
                        'password' => $kataSandi,
                        'remember_token' => Str::random(60),
                    ])->save();

                    event(new PasswordReset($user));
                },
            )
            : Password::INVALID_TOKEN;

        if ($status !== Password::PASSWORD_RESET) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Tautan atur ulang tidak valid atau sudah kedaluwarsa. Minta tautan baru.']);
        }

        return redirect()->route('admin.login')->with('status_masuk', 'Kata sandi berhasil diubah. Silakan masuk dengan kata sandi baru.');
    }

    public function keluar(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    private function kunciPercobaan(Request $request): string
    {
        return Str::transliterate(Str::lower($request->string('login')->toString()).'|'.$request->ip());
    }
}
