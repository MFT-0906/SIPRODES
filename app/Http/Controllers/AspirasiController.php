<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AspirasiController extends Controller
{
    public function create(): View
    {
        return view('pages.public.aspirasi');
    }

    public function store(Request $request): RedirectResponse
    {
        // Validasi di sini wajib diulang di server; validasi di sisi klien hanya untuk kenyamanan.
        $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'nik' => ['nullable', 'digits:16'],
            'pesan' => ['required', 'string', 'min:20', 'max:1000'],
        ], [
            'nama.required' => 'Nama wajib diisi.',
            'nama.max' => 'Nama paling banyak :max karakter.',
            'nik.digits' => 'NIK harus terdiri dari 16 digit angka. Kosongkan jika tidak diperlukan.',
            'pesan.required' => 'Isi pesan wajib diisi.',
            'pesan.min' => 'Pesan terlalu pendek. Tulis minimal :min karakter.',
            'pesan.max' => 'Pesan terlalu panjang. Tulis paling banyak :max karakter.',
        ]);

        // TODO(backend): simpan aspirasi (nama, pesan, waktu kirim, status "Baru"; NIK hanya bila benar-benar
        // dibutuhkan dan sudah ada kebijakan perlindungannya) dan terapkan pembatasan spam yang disepakati.

        return redirect()->route('aspirasi.create')->with('aspirasi_terkirim', true);
    }
}
