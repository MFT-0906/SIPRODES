<?php

namespace App\Http\Controllers;

use App\Models\Dusun;
use App\Models\Jabatan;
use App\Support\Aktivitas;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Data master Dusun dan Jabatan (khusus Super Admin; rute dilindungi middleware `modul:akun`).
 * Nilai awal berasal dari MasterDataSeeder dan menunggu konfirmasi desa.
 */
class AdminMasterController extends Controller
{
    private const JENIS = [
        'dusun' => [Dusun::class, 'dusun', 'Dusun'],
        'jabatan' => [Jabatan::class, 'jabatan', 'Jabatan'],
    ];

    public function index(): View
    {
        return view('pages.admin.master', [
            'dusun' => Dusun::query()->withCount('kelompokTani')->orderBy('urutan')->orderBy('nama')->get(),
            'jabatan' => Jabatan::query()->withCount('perangkat')->orderBy('urutan')->orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request, string $jenis): RedirectResponse
    {
        [$kelas, $tabel, $label] = $this->jenis($jenis);
        $data = $this->validasi($request, $tabel, null, $label);

        /** @var Model $baris */
        $baris = $kelas::query()->create($data + ['urutan' => (int) $kelas::query()->max('urutan') + 1]);
        Aktivitas::catat('master.dibuat', $baris, $label.': '.$baris->nama);

        return $this->selesai("{$label} berhasil ditambahkan.");
    }

    public function update(Request $request, string $jenis, int $id): RedirectResponse
    {
        [$kelas, $tabel, $label] = $this->jenis($jenis);
        $baris = $kelas::query()->findOrFail($id);
        $baris->update($this->validasi($request, $tabel, $id, $label));
        Aktivitas::catat('master.diubah', $baris, $label.': '.$baris->nama);

        return $this->selesai('Perubahan berhasil disimpan.');
    }

    public function destroy(string $jenis, int $id): RedirectResponse
    {
        [$kelas, , $label] = $this->jenis($jenis);
        $baris = $kelas::query()->findOrFail($id);

        if ($jenis === 'jabatan' && $baris->perangkat()->exists()) {
            return redirect()->route('admin.master')->with('toast', 'Jabatan masih dipakai perangkat desa. Ubah atau hapus perangkatnya lebih dulu.');
        }

        $baris->delete();
        Aktivitas::catat('master.dihapus', $baris, $label.': '.$baris->nama);

        return $this->selesai("{$label} berhasil dihapus.");
    }

    /**
     * @return array{0: class-string<Model>, 1: string, 2: string}
     */
    private function jenis(string $jenis): array
    {
        return self::JENIS[$jenis] ?? abort(404);
    }

    /**
     * @return array{nama: string, urutan?: int}
     */
    private function validasi(Request $request, string $tabel, ?int $abaikan, string $label): array
    {
        $valid = $request->validate([
            'nama' => ['required', 'string', 'max:100', Rule::unique($tabel, 'nama')->ignore($abaikan)],
            'urutan' => ['nullable', 'integer', 'between:0,999'],
        ], [
            'required' => ':attribute wajib diisi.',
            'max' => ':attribute terlalu panjang (maksimal :max karakter).',
            'unique' => ':attribute sudah ada.',
            'integer' => 'Urutan harus berupa angka.',
            'between' => 'Urutan di luar rentang yang benar.',
        ], ['nama' => 'Nama '.mb_strtolower($label)]);

        $hasil = ['nama' => trim($valid['nama'])];
        if (isset($valid['urutan'])) {
            $hasil['urutan'] = (int) $valid['urutan'];
        }

        return $hasil;
    }

    private function selesai(string $pesan): RedirectResponse
    {
        return redirect()->route('admin.master')->with('toast', $pesan);
    }
}
