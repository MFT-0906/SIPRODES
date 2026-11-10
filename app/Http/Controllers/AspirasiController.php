<?php

namespace App\Http\Controllers;

use App\Enums\StatusAspirasi;
use App\Http\Requests\SimpanAspirasiRequest;
use App\Models\Aspirasi;
use App\Support\AntiSpamAspirasi;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

/**
 * Ruang Aspirasi (publik). Aspirasi tidak pernah ditampilkan ke publik; NIK tidak diminta dan tidak ada IP mentah.
 */
class AspirasiController extends Controller
{
    public function create(): View
    {
        // Pertanyaan hitungan baru setiap kali formulir ditampilkan (termasuk setelah isian ditolak).
        // Jawaban lama tidak diisikan kembali karena pertanyaannya sudah berganti.
        session()->forget('_old_input.'.AntiSpamAspirasi::KOLOM_JAWABAN);

        return view('pages.public.aspirasi', ['soal' => AntiSpamAspirasi::soalBaru()]);
    }

    public function store(SimpanAspirasiRequest $request): RedirectResponse
    {
        // Honeypot terisi: tampak berhasil bagi pengirim, tetapi tidak ada yang disimpan.
        if ($request->jebakanTerisi()) {
            return redirect()->route('aspirasi.create')->with('aspirasi_terkirim', true);
        }

        DB::transaction(function () use ($request) {
            $aspirasi = Aspirasi::query()->create([
                'nama' => $request->string('nama')->toString(),
                'pesan' => $request->string('pesan')->toString(),
                'status' => StatusAspirasi::Baru,
            ]);

            $aspirasi->riwayat()->create(['aksi' => 'diterima', 'catatan' => null]);
        });

        return redirect()->route('aspirasi.create')->with('aspirasi_terkirim', true);
    }
}
