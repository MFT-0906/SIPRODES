<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminAuthController extends Controller
{
    // TODO(backend): autentikasi sungguhan (guard, penguncian akun setelah gagal berulang, kirim email atur ulang).

    public function login(): View
    {
        return view('pages.admin.login');
    }

    public function masuk(Request $request): RedirectResponse
    {
        $request->validate([
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
        ], [
            'login.required' => 'Email atau username wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        // Hanya untuk meninjau tampilan: di lokal, formulir yang lengkap membuka dashboard.
        // Di lingkungan lain selalu gagal dengan pesan umum (tidak membocorkan akun mana yang ada).
        if (app()->environment('local')) {
            return redirect()->route('admin.dashboard');
        }

        return back()
            ->withInput($request->only('login'))
            ->with('gagal_masuk', 'Email/username atau kata sandi tidak sesuai. Silakan coba lagi.');
    }

    public function lupa(): View
    {
        return view('pages.admin.lupa-kata-sandi');
    }

    public function kirimTautan(Request $request): RedirectResponse
    {
        $request->validate(
            ['email' => ['required', 'email', 'max:255']],
            ['email.required' => 'Email wajib diisi.', 'email.email' => 'Format email belum benar.'],
        );

        // TODO(backend): kirim tautan hanya bila email terdaftar; respons tetap sama untuk email terdaftar/tidak.
        return back()->with('tautan_terkirim', true);
    }

    public function keluar(): RedirectResponse
    {
        // TODO(backend): akhiri sesi pengguna.
        return redirect()->route('admin.login');
    }
}
