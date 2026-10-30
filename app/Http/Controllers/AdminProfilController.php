<?php

namespace App\Http\Controllers;

use App\Support\DemoContent;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminProfilController extends Controller
{
    // TODO(backend): middleware `auth`, pengecekan izin, dan penyimpanan ke database.
    // Lokasi dan kontak resmi hanya tampil di publik setelah dicentang "sudah diverifikasi".

    private const TAB = ['sejarah', 'visi', 'lokasi', 'data'];

    public function show(Request $request): View
    {
        $tab = in_array($request->query('tab'), self::TAB, true) ? $request->query('tab') : 'sejarah';

        return view('pages.admin.profil', ['tab' => $tab, 'data' => $this->data()]);
    }

    public function simpan(Request $request, string $tab): RedirectResponse
    {
        abort_unless(in_array($tab, self::TAB, true), 404);

        $valid = match ($tab) {
            'sejarah' => $request->validate(
                ['sejarah' => ['required', 'string', 'max:20000']],
                ['required' => ':attribute wajib diisi.', 'max' => ':attribute terlalu panjang.'],
                ['sejarah' => 'Naskah sejarah'],
            ),
            'visi' => $request->validate(
                ['visi' => ['required', 'string', 'max:500'], 'misi' => ['nullable', 'array', 'max:20'], 'misi.*' => ['nullable', 'string', 'max:300']],
                ['required' => ':attribute wajib diisi.', 'max' => ':attribute terlalu panjang.'],
                ['visi' => 'Visi', 'misi.*' => 'Butir misi'],
            ),
            'lokasi' => $request->validate(
                [
                    'alamat' => ['required', 'string', 'max:300'],
                    'lintang' => ['required', 'numeric', 'between:-90,90'],
                    'bujur' => ['required', 'numeric', 'between:-180,180'],
                    'telepon' => ['nullable', 'regex:/^[0-9 ()+\-]{5,20}$/'],
                    'email' => ['nullable', 'email', 'max:150'],
                    'terverifikasi' => ['nullable', 'boolean'],
                    'aksi' => ['nullable', 'in:draf,terbit'],
                ],
                [
                    'required' => ':attribute wajib diisi.', 'numeric' => ':attribute harus berupa angka koordinat.',
                    'between' => ':attribute di luar rentang yang benar.', 'regex' => ':attribute belum berupa nomor telepon yang benar.',
                    'email' => ':attribute belum berupa alamat email yang benar.', 'max' => ':attribute terlalu panjang.',
                ],
                ['alamat' => 'Alamat kantor desa', 'lintang' => 'Lintang', 'bujur' => 'Bujur', 'telepon' => 'Telepon kantor', 'email' => 'Email resmi'],
            ),
            default => $request->validate(
                [
                    'penduduk' => ['required', 'integer', 'min:0'], 'kk' => ['required', 'integer', 'min:0'],
                    'luas' => ['required', 'integer', 'min:0'], 'dusun' => ['required', 'integer', 'min:0'],
                    'sumber' => ['required', 'string', 'max:150'], 'terverifikasi' => ['nullable', 'boolean'],
                ],
                ['required' => ':attribute wajib diisi.', 'integer' => ':attribute harus berupa bilangan bulat.', 'min' => ':attribute tidak boleh negatif.'],
                ['penduduk' => 'Jumlah penduduk', 'kk' => 'Kepala keluarga', 'luas' => 'Luas wilayah', 'dusun' => 'Jumlah dusun', 'sumber' => 'Sumber data'],
            ),
        };

        // TODO(backend): simpan ke database. Di sini hanya disimpan di sesi agar nilai terlihat setelah disimpan.
        session(['demo.profil' => [$tab => $valid] + session('demo.profil', [])]);

        return redirect()->route('admin.profil', ['tab' => $tab])->with('toast', 'Perubahan berhasil disimpan.');
    }

    /** @return array<string, mixed> */
    private function data(): array
    {
        $profil = DemoContent::profil();
        $angka = fn (string $teks) => (int) preg_replace('/\D/', '', $teks);
        $fakta = collect($profil['fakta'])->pluck('value', 'label');

        $bawaan = [
            'sejarah' => ['sejarah' => $profil['sejarah']['utama']."\n\n".$profil['sejarah']['lanjutan']],
            'visi' => ['visi' => null, 'misi' => []],
            'lokasi' => ['alamat' => null, 'lintang' => null, 'bujur' => null, 'telepon' => null, 'email' => null, 'terverifikasi' => false],
            'data' => [
                'penduduk' => $angka($fakta['Jumlah penduduk']), 'kk' => $angka($fakta['Kepala keluarga']),
                'luas' => $angka($fakta['Luas wilayah']), 'dusun' => $angka($fakta['Jumlah dusun']),
                'sumber' => 'Survei awal 2026', 'terverifikasi' => false,
            ],
        ];

        return array_merge($bawaan, session('demo.profil', []));
    }
}
