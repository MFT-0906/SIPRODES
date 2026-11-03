<?php

namespace App\Http\Controllers;

use App\Enums\StatusAspirasi;
use App\Http\Requests\SimpanAspirasiRequest;
use App\Models\Aspirasi;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

/**
 * Ruang Aspirasi (publik). Aspirasi tidak pernah ditampilkan ke publik; NIK opsional, terenkripsi, dan tidak ada IP mentah.
 */
class AspirasiController extends Controller
{
    public function create(): View
    {
        return view('pages.public.aspirasi');
    }

    public function store(SimpanAspirasiRequest $request): RedirectResponse
    {
        // Honeypot terisi: tampak berhasil bagi pengirim, tetapi tidak ada yang disimpan.
        if ($request->jebakanTerisi()) {
            return redirect()->route('aspirasi.create')->with('aspirasi_terkirim', true);
        }

        $nik = config('desa.aspirasi.nik_aktif') && $request->filled('nik') ? $request->string('nik')->toString() : null;

        DB::transaction(function () use ($request, $nik) {
            $aspirasi = Aspirasi::query()->create([
                'nama' => $request->string('nama')->toString(),
                'nik' => $nik,
                'pesan' => $request->string('pesan')->toString(),
                'status' => StatusAspirasi::Baru,
            ]);

            $aspirasi->riwayat()->create(['aksi' => 'diterima', 'catatan' => null]);
        });

        return redirect()->route('aspirasi.create')->with('aspirasi_terkirim', true);
    }
}
