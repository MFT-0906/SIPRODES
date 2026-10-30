<?php

namespace App\Http\Controllers;

use App\Support\DemoStore;
use App\Support\Peran;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminAkunController extends Controller
{
    // TODO(backend): izin Super Admin WAJIB ditegakkan di server (middleware/policy); pemeriksaan di sini
    // hanya mengikuti peran contoh. Kata sandi tidak pernah diisi admin: pengguna baru menerima tautan.

    public function index(Request $request): View
    {
        $this->pastikanSuperAdmin();

        return view('pages.admin.akun', [
            'daftar' => DemoStore::daftar('akun'),
            'ubah' => DemoStore::item('akun', $request->query('ubah')),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->pastikanSuperAdmin();

        // TODO(backend): buat akun tanpa kata sandi, kirim tautan pembuatan kata sandi ke pengguna.
        DemoStore::simpan('akun', $this->data($request) + ['aktif' => true]);

        return redirect()->route('admin.akun.index')->with('toast', 'Akun dibuat. Pengguna akan menerima tautan untuk membuat kata sandi.');
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $this->pastikanSuperAdmin();
        abort_if(DemoStore::item('akun', $id) === null, 404);

        DemoStore::simpan('akun', $this->data($request) + ['aktif' => $request->boolean('aktif')], $id);

        return redirect()->route('admin.akun.index')->with('toast', 'Perubahan berhasil disimpan.');
    }

    public function reset(string $id): RedirectResponse
    {
        $this->pastikanSuperAdmin();
        abort_if(DemoStore::item('akun', $id) === null, 404);

        // TODO(backend): kirim tautan atur ulang kata sandi; pengguna membuat kata sandinya sendiri.
        return redirect()->route('admin.akun.index')->with('toast', 'Tautan atur ulang kata sandi dikirim ke pengguna.');
    }

    private function pastikanSuperAdmin(): void
    {
        abort_unless(Peran::superAdmin(), 403);
    }

    /** @return array{nama: string, email: string, peran: string} */
    private function data(Request $request): array
    {
        $valid = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'max:150'],
            'peran' => ['required', 'in:staf,super_admin'],
        ], [
            'required' => ':attribute wajib diisi.', 'max' => ':attribute terlalu panjang (maksimal :max karakter).', 'in' => ':attribute tidak dikenal.',
        ], ['nama' => 'Nama lengkap', 'email' => 'Email atau username', 'peran' => 'Peran']);

        return ['nama' => $valid['nama'], 'email' => $valid['email'], 'peran' => $valid['peran']];
    }
}
