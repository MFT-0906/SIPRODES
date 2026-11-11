<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\Aktivitas;
use App\Support\GoogleLogin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * "Masuk dengan Google" untuk dashboard admin.
 *
 * Aturan: hanya email yang sudah didaftarkan Super Admin dan berstatus aktif yang boleh masuk; Google tidak
 * pernah membuat akun baru. Saat pertama berhasil, akun Google (klaim `sub`) ditautkan ke akun admin, sehingga
 * selanjutnya hanya akun Google yang sama yang diterima. Login dengan kata sandi tetap tersedia sebagai cadangan.
 */
class GoogleLoginController extends Controller
{
    private const SESI = 'google_oauth';

    private const PESAN_GAGAL = 'Masuk dengan Google belum berhasil. Coba lagi, atau masuk dengan kata sandi.';

    private const PESAN_TIDAK_TERDAFTAR = 'Akun Google ini belum terdaftar sebagai admin. Minta Super Admin mendaftarkan email Anda di menu Akun & Hak Akses.';

    public function arahkan(Request $request): RedirectResponse
    {
        abort_unless(GoogleLogin::aktif(), 404);

        $state = Str::random(40);
        $verifier = Str::random(64);
        $request->session()->put(self::SESI, ['state' => $state, 'verifier' => $verifier, 'dibuat' => now()->timestamp]);

        return redirect()->away(GoogleLogin::alamatOtorisasi($state, $verifier));
    }

    public function kembali(Request $request): RedirectResponse
    {
        abort_unless(GoogleLogin::aktif(), 404);

        // Sekali pakai: state dan verifier langsung dihapus dari sesi.
        $sesi = $request->session()->pull(self::SESI);
        $state = $request->query('state');

        $sah = is_array($sesi)
            && is_string($state)
            && hash_equals((string) ($sesi['state'] ?? ''), $state)
            && now()->timestamp - (int) ($sesi['dibuat'] ?? 0) <= 600;

        if (! $sah) {
            return $this->gagal(self::PESAN_GAGAL);
        }

        if ($request->filled('error')) {
            return $this->gagal('Masuk dengan Google dibatalkan.');
        }

        $kode = $request->query('code');
        $profil = is_string($kode) && $kode !== '' ? GoogleLogin::profil($kode, (string) $sesi['verifier']) : null;
        if ($profil === null) {
            return $this->gagal(self::PESAN_GAGAL);
        }

        if (! $profil['email_verified']) {
            Aktivitas::catat('masuk.google_ditolak', null, $profil['email'].' (email Google belum terverifikasi)');

            return $this->gagal(self::PESAN_TIDAK_TERDAFTAR);
        }

        $user = User::query()->whereRaw('LOWER(email) = ?', [$profil['email']])->where('aktif', true)->first();
        if ($user === null) {
            Aktivitas::catat('masuk.google_ditolak', null, $profil['email'].' (tidak terdaftar atau nonaktif)');

            return $this->gagal(self::PESAN_TIDAK_TERDAFTAR);
        }

        if ($user->google_id !== null && ! hash_equals($user->google_id, $profil['sub'])) {
            Aktivitas::catat('masuk.google_ditolak', $user, $profil['email'].' (akun Google berbeda dari yang tertaut)');

            return $this->gagal('Akun Google ini berbeda dengan yang tertaut ke akun admin Anda. Hubungi Super Admin.');
        }

        // Satu akun Google hanya boleh tertaut ke satu akun admin.
        if ($user->google_id === null && User::query()->where('google_id', $profil['sub'])->exists()) {
            Aktivitas::catat('masuk.google_ditolak', $user, $profil['email'].' (akun Google sudah tertaut ke akun lain)');

            return $this->gagal('Akun Google ini sudah tertaut ke akun admin lain. Hubungi Super Admin.');
        }

        Auth::login($user);
        $request->session()->regenerate();
        $user->forceFill(['google_id' => $profil['sub'], 'terakhir_masuk_at' => now()])->save();
        Aktivitas::catat('masuk.google', $user);

        return redirect()->intended(route('admin.dashboard'));
    }

    private function gagal(string $pesan): RedirectResponse
    {
        return redirect()->route('admin.login')->with('gagal_masuk', $pesan);
    }
}
