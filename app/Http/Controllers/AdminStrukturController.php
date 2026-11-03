<?php

namespace App\Http\Controllers;

use App\Support\DemoContent;
use App\Support\DemoStore;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminStrukturController extends Controller
{
    // TODO(backend): penyimpanan foto dan data ke database.

    public function index(Request $request): View
    {
        $ubah = DemoStore::item('struktur', $request->query('ubah'));

        return view('pages.admin.struktur', [
            'daftar' => DemoStore::daftar('struktur'),
            'item' => $ubah,
            'jabatan' => $this->jabatan(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        DemoStore::simpan('struktur', $this->data($request));

        return $this->selesai('Perubahan berhasil disimpan.');
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        abort_if(DemoStore::item('struktur', $id) === null, 404);

        DemoStore::simpan('struktur', $this->data($request), $id);

        return $this->selesai('Perubahan berhasil disimpan.');
    }

    public function destroy(string $id): RedirectResponse
    {
        abort_if(DemoStore::item('struktur', $id) === null, 404);

        DemoStore::hapus('struktur', $id);

        return $this->selesai('Perangkat berhasil dihapus.');
    }

    public function geser(Request $request, string $id): RedirectResponse
    {
        abort_if(DemoStore::item('struktur', $id) === null, 404);
        $arah = $request->validate(['arah' => ['required', 'in:naik,turun']])['arah'];

        DemoStore::geser('struktur', $id, $arah === 'naik' ? -1 : 1);

        return $this->selesai('Urutan berhasil diubah.');
    }

    private function selesai(string $pesan): RedirectResponse
    {
        return redirect()->route('admin.struktur')->with('toast', $pesan);
    }

    /** @return list<string> */
    private function jabatan(): array
    {
        // TODO(backend): daftar jabatan dari data master desa.
        return collect(DemoContent::profil()['perangkat']['daftar'])->pluck('position')->unique()->values()->all();
    }

    /** @return array<string, mixed> */
    private function data(Request $request): array
    {
        $valid = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'jabatan' => ['required', Rule::in($this->jabatan())],
            'tampil' => ['nullable', 'boolean'],
            // TODO(backend): periksa isi berkas, batas ukuran, dan simpan foto (rasio 1:1).
            'foto' => ['nullable', 'file', 'mimes:jpg,jpeg,png'],
        ], [
            'required' => ':attribute wajib diisi.',
            'max' => ':attribute terlalu panjang (maksimal :max karakter).',
            'in' => ':attribute tidak dikenal.',
            'mimes' => 'Foto harus berformat JPG atau PNG.',
        ], ['nama' => 'Nama lengkap', 'jabatan' => 'Jabatan']);

        return ['nama' => $valid['nama'], 'jabatan' => $valid['jabatan'], 'tampil' => (bool) ($valid['tampil'] ?? false)];
    }
}
