<?php

namespace App\Http\Controllers;

use App\Enums\Modul;
use App\Enums\Peran;
use App\Http\Requests\Admin\SimpanAkunRequest;
use App\Models\LogAktivitas;
use App\Models\User;
use App\Notifications\UndanganAkun;
use App\Support\Aktivitas;
use App\Support\TautanUndangan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
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
            'log' => $this->daftarLog($request),
            'labelAksi' => Aktivitas::LABEL,
            'filterAksi' => $this->aksiDipilih($request),
            'kataLog' => mb_substr(trim((string) $request->query('qlog', '')), 0, 100),
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

        Aktivitas::catat('akun.dibuat', $user, $user->email.' · peran '.$user->peran->label().$this->ringkasModul($user));

        if ($this->kirimUndangan($user) !== Password::RESET_THROTTLED) {
            Aktivitas::catat('akun.undangan_dikirim', $user, $user->email);
        }

        return redirect()->route('admin.akun.index')->with('toast', 'Akun dibuat. Pengguna menerima email undangan untuk membuat kata sandi.');
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

        $sebelum = $this->potret($user);
        $emailBaru = $request->string('email')->toString();
        // Tautan Google dilepas bila email diganti (akun Google lama tidak lagi berlaku) atau diminta Super Admin.
        $lepasGoogle = $user->google_id !== null
            && ($request->boolean('lepas_google') || mb_strtolower($emailBaru) !== mb_strtolower($user->email));

        DB::transaction(function () use ($request, $user, $peranBaru, $aktifBaru, $lepasGoogle) {
            if ($lepasGoogle) {
                $user->forceFill(['google_id' => null]);
            }
            $user->update([
                'name' => $request->string('nama')->toString(),
                'email' => $request->string('email')->toString(),
                'username' => $request->filled('username') ? $request->string('username')->toString() : null,
                'peran' => $peranBaru,
                'aktif' => $aktifBaru,
            ]);
            $this->sinkronkanModul($user, $request);
        });

        Aktivitas::catat('akun.diubah', $user, $user->email.$this->selisih($sebelum, $this->potret($user->refresh())));
        if ($lepasGoogle) {
            Aktivitas::catat('akun.google_dilepas', $user, $user->email);
        }

        return redirect()->route('admin.akun.index')->with('toast', 'Perubahan berhasil disimpan.');
    }

    public function reset(User $user): RedirectResponse
    {
        if (! $user->aktif) {
            return redirect()->route('admin.akun.index')->with('toast', 'Akun nonaktif tidak dapat dikirimi tautan. Aktifkan akun lebih dulu.');
        }

        // Pengguna yang belum pernah masuk masih menunggu undangan; yang sudah pernah masuk menerima email atur ulang biasa.
        $undangan = $user->terakhir_masuk_at === null;
        $status = $undangan
            ? $this->kirimUndangan($user)
            : Password::sendResetLink(['email' => $user->email]);

        if ($status === Password::RESET_THROTTLED) {
            return redirect()->route('admin.akun.index')->with('toast', 'Tautan baru sudah dikirim barusan. Coba lagi dalam satu menit.');
        }

        Aktivitas::catat($undangan ? 'akun.undangan_dikirim' : 'akun.tautan_dikirim', $user, $user->email);

        return redirect()->route('admin.akun.index')->with('toast', $undangan
            ? 'Undangan dikirim ulang. Pengguna menerima email untuk membuat kata sandi.'
            : 'Tautan atur ulang kata sandi dikirim ke pengguna.');
    }

    /**
     * Mengirim email undangan (broker "undangan", masa berlaku lebih panjang). Token tidak pernah dicatat.
     */
    private function kirimUndangan(User $user): string
    {
        return Password::broker(TautanUndangan::BROKER)->sendResetLink(
            ['email' => $user->email],
            fn (User $penerima, string $token) => $penerima->notify(new UndanganAkun($token)),
        );
    }

    /**
     * @return array{peran: string, aktif: bool, modul: list<string>}
     */
    private function potret(User $user): array
    {
        $modul = $user->modulAkses()->get()->map(fn ($m) => $m->modul->value)->sort()->values()->all();

        return ['peran' => $user->peran->label(), 'aktif' => (bool) $user->aktif, 'modul' => $modul];
    }

    /**
     * Ringkasan perubahan peran, status, dan modul untuk audit log.
     *
     * @param  array{peran: string, aktif: bool, modul: list<string>}  $a
     * @param  array{peran: string, aktif: bool, modul: list<string>}  $b
     */
    private function selisih(array $a, array $b): string
    {
        $bagian = [];
        if ($a['peran'] !== $b['peran']) {
            $bagian[] = 'peran '.$a['peran'].' → '.$b['peran'];
        }
        if ($a['aktif'] !== $b['aktif']) {
            $bagian[] = $b['aktif'] ? 'diaktifkan' : 'dinonaktifkan';
        }
        if ($a['modul'] !== $b['modul']) {
            $bagian[] = 'modul ['.implode(', ', $a['modul']).'] → ['.implode(', ', $b['modul']).']';
        }

        return $bagian === [] ? ' · data diri diubah' : ' · '.implode('; ', $bagian);
    }

    private function ringkasModul(User $user): string
    {
        $modul = $user->modulAkses()->get()->map(fn ($m) => $m->modul->value)->all();

        return $modul === [] ? '' : ' · modul '.implode(', ', $modul);
    }

    /**
     * Log aktivitas terbaru dengan filter jenis aksi (`aksi`) dan kata kunci keterangan (`qlog`), 20 per halaman.
     *
     * @return LengthAwarePaginator<int, LogAktivitas>
     */
    private function daftarLog(Request $request): LengthAwarePaginator
    {
        $aksi = $this->aksiDipilih($request);

        return LogAktivitas::query()->with('user')
            ->when($aksi !== null, fn ($q) => $q->where('aksi', $aksi))
            ->cari(['keterangan'], (string) $request->query('qlog', ''))
            ->latest('id')
            ->paginate(20, pageName: 'halaman_log')
            ->withQueryString()
            ->fragment('log-judul');
    }

    private function aksiDipilih(Request $request): ?string
    {
        $aksi = $request->query('aksi');

        return is_string($aksi) && array_key_exists($aksi, Aktivitas::LABEL) ? $aksi : null;
    }

    /**
     * Menghapus akun secara permanen. Riwayatnya tidak hilang: berita, dokumen APBDes, riwayat aspirasi, dan
     * Log Aktivitas menyimpan kolom pelaku sebagai kosong (nullOnDelete), sedangkan log tetap memuat nama pelaku
     * di kolom pelaku_nama. Super Admin tidak bisa menghapus akunnya sendiri maupun Super Admin aktif terakhir.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->is($user)) {
            return redirect()->route('admin.akun.index')->with('toast', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        if ($user->adalahSuperAdmin() && $user->aktif && ! $this->adaSuperAdminAktifLain($user)) {
            return redirect()->route('admin.akun.index')->with('toast', 'Harus ada minimal satu Super Admin aktif. Akun ini tidak dapat dihapus.');
        }

        $ringkas = $user->name.' · '.$user->email.' · peran '.$user->peran->label();

        DB::transaction(function () use ($user) {
            // Putuskan sesi yang masih terbuka dan tautan atur ulang kata sandi yang belum dipakai.
            if (config('session.driver') === 'database') {
                DB::table(config('session.table', 'sessions'))->where('user_id', $user->getKey())->delete();
            }
            DB::table(config('auth.passwords.users.table', 'password_reset_tokens'))->where('email', $user->email)->delete();
            $user->delete();
        });

        Aktivitas::catat('akun.dihapus', null, $ringkas);

        return redirect()->route('admin.akun.index')->with('toast', 'Akun “'.$user->name.'” dihapus.');
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
