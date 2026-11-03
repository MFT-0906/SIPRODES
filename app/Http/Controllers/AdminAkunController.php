<?php

namespace App\Http\Controllers;

use App\Enums\Modul;
use App\Enums\Peran;
use App\Http\Requests\Admin\SimpanAkunRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

/**
 * Modul Akun & Hak Akses. Rute dilindungi middleware `modul:akun` (khusus Super Admin).
 * Admin tidak pernah mengisi kata sandi: pengguna menerima tautan untuk membuatnya sendiri.
 */
class AdminAkunController extends Controller
{
    public function index(Request $request): View
    {
        $ubah = $request->filled('ubah') ? User::query()->with('modulAkses')->find($request->query('ubah')) : null;

        return view('pages.admin.akun', [
            'daftar' => User::query()->with('modulAkses')->orderBy('name')->get(),
            'ubah' => $ubah,
            'modulStaf' => Modul::untukStaf(),
        ]);
    }

    public function store(SimpanAkunRequest $request): RedirectResponse
    {
        $user = DB::transaction(function () use ($request) {
            $user = User::query()->create([
                'name' => $request->string('nama')->toString(),
                'email' => $request->string('email')->toString(),
                'username' => $request->filled('username') ? $request->string('username')->toString() : null,
                // Kata sandi acak yang tidak diketahui siapa pun; pengguna membuat sendiri lewat tautan.
                'password' => Str::random(40),
                'peran' => Peran::from($request->string('peran')->toString()),
                'aktif' => true,
            ]);
            $this->sinkronkanModul($user, $request);

            return $user;
        });

        Password::sendResetLink(['email' => $user->email]);

        return redirect()->route('admin.akun.index')->with('toast', 'Akun dibuat. Pengguna akan menerima tautan untuk membuat kata sandi.');
    }

    public function update(SimpanAkunRequest $request, User $user): RedirectResponse
    {
        $peranBaru = Peran::from($request->string('peran')->toString());
        $aktifBaru = $request->boolean('aktif');

        if ($request->user()->is($user) && ! $aktifBaru) {
            return $this->tolak('aktif', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        $kehilanganSuperAdmin = $user->adalahSuperAdmin() && $user->aktif && ($peranBaru !== Peran::SuperAdmin || ! $aktifBaru);
        if ($kehilanganSuperAdmin && ! $this->adaSuperAdminAktifLain($user)) {
            return $this->tolak('peran', 'Harus ada minimal satu Super Admin aktif. Jadikan akun lain Super Admin lebih dulu.');
        }

        DB::transaction(function () use ($request, $user, $peranBaru, $aktifBaru) {
            $user->update([
                'name' => $request->string('nama')->toString(),
                'email' => $request->string('email')->toString(),
                'username' => $request->filled('username') ? $request->string('username')->toString() : null,
                'peran' => $peranBaru,
                'aktif' => $aktifBaru,
            ]);
            $this->sinkronkanModul($user, $request);
        });

        return redirect()->route('admin.akun.index')->with('toast', 'Perubahan berhasil disimpan.');
    }

    public function reset(User $user): RedirectResponse
    {
        if (! $user->aktif) {
            return redirect()->route('admin.akun.index')->with('toast', 'Akun nonaktif tidak dapat dikirimi tautan. Aktifkan akun lebih dulu.');
        }

        $status = Password::sendResetLink(['email' => $user->email]);

        return redirect()->route('admin.akun.index')->with('toast', $status === Password::RESET_THROTTLED
            ? 'Tautan baru sudah dikirim barusan. Coba lagi dalam satu menit.'
            : 'Tautan atur ulang kata sandi dikirim ke pengguna.');
    }

    private function adaSuperAdminAktifLain(User $user): bool
    {
        return User::query()
            ->where('peran', Peran::SuperAdmin->value)
            ->where('aktif', true)
            ->whereKeyNot($user->getKey())
            ->exists();
    }

    /**
     * Staf memegang modul yang dipilih; Super Admin memegang semuanya sehingga tidak butuh penugasan.
     */
    private function sinkronkanModul(User $user, Request $request): void
    {
        $user->modulAkses()->delete();

        if ($user->peran !== Peran::Staf) {
            return;
        }

        foreach (array_unique((array) $request->input('modul', [])) as $modul) {
            $user->modulAkses()->create(['modul' => $modul]);
        }
    }

    private function tolak(string $kolom, string $pesan): RedirectResponse
    {
        return back()->withInput()->withErrors([$kolom => $pesan]);
    }
}
