<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\SimpanPerangkatRequest;
use App\Models\Jabatan;
use App\Models\PerangkatDesa;
use App\Support\UnggahBerkas;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Kelola Struktur Perangkat Desa. Rute dilindungi middleware `modul:profil`.
 */
class AdminStrukturController extends Controller
{
    public function index(Request $request): View
    {
        $ubah = $request->filled('ubah') ? PerangkatDesa::query()->with('jabatan')->find($request->query('ubah')) : null;

        return view('pages.admin.struktur', [
            'daftar' => $this->urutan()->map(fn (PerangkatDesa $p) => $this->larik($p))->all(),
            'item' => $ubah ? $this->larik($ubah) : null,
            'jabatan' => Jabatan::query()->orderBy('urutan')->pluck('nama')->all(),
        ]);
    }

    public function store(SimpanPerangkatRequest $request): RedirectResponse
    {
        $perangkat = new PerangkatDesa;
        $perangkat->urutan = ((int) PerangkatDesa::query()->max('urutan')) + 1;
        $this->isi($perangkat, $request)->save();

        return $this->selesai('Perubahan berhasil disimpan.');
    }

    public function update(SimpanPerangkatRequest $request, int $id): RedirectResponse
    {
        $perangkat = PerangkatDesa::query()->findOrFail($id);
        $fotoLama = $perangkat->foto_path;
        $this->isi($perangkat, $request)->save();

        if ($fotoLama && $fotoLama !== $perangkat->foto_path) {
            UnggahBerkas::hapusGambar($fotoLama);
        }

        return $this->selesai('Perubahan berhasil disimpan.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $perangkat = PerangkatDesa::query()->findOrFail($id);
        $foto = $perangkat->foto_path;
        $perangkat->delete();
        UnggahBerkas::hapusGambar($foto);

        return $this->selesai('Perangkat berhasil dihapus.');
    }

    /**
     * Menggeser satu langkah. Seluruh urutan dinomori ulang dalam satu transaksi agar tidak ada nilai kembar.
     */
    public function geser(Request $request, int $id): RedirectResponse
    {
        $arah = $request->validate(['arah' => ['required', 'in:naik,turun']])['arah'];

        DB::transaction(function () use ($id, $arah) {
            $daftar = PerangkatDesa::query()->orderBy('urutan')->orderBy('id')->lockForUpdate()->get()->values();
            $posisi = $daftar->search(fn (PerangkatDesa $p) => $p->id === $id);
            abort_if($posisi === false, 404);

            $tujuan = $posisi + ($arah === 'naik' ? -1 : 1);
            $daftar = $daftar->all();
            if (isset($daftar[$tujuan])) {
                [$daftar[$posisi], $daftar[$tujuan]] = [$daftar[$tujuan], $daftar[$posisi]];
            }

            foreach ($daftar as $i => $p) {
                if ($p->urutan !== $i + 1) {
                    $p->update(['urutan' => $i + 1]);
                }
            }
        });

        return $this->selesai('Urutan berhasil diubah.');
    }

    private function isi(PerangkatDesa $perangkat, SimpanPerangkatRequest $request): PerangkatDesa
    {
        $perangkat->nama = $request->string('nama')->toString();
        $perangkat->jabatan_id = Jabatan::query()->where('nama', $request->input('jabatan'))->value('id');
        $perangkat->tampil = $request->boolean('tampil');
        // Data yang diinput admin adalah data nyata, bukan contoh.
        $perangkat->contoh = false;

        if ($request->hasFile('foto')) {
            $perangkat->foto_path = UnggahBerkas::simpanGambar($request->file('foto'), 'perangkat');
        } elseif ($request->boolean('hapus_foto')) {
            $perangkat->foto_path = null;
        }

        return $perangkat;
    }

    private function selesai(string $pesan): RedirectResponse
    {
        return redirect()->route('admin.struktur')->with('toast', $pesan);
    }

    /** @return Collection<int, PerangkatDesa> */
    private function urutan(): Collection
    {
        return PerangkatDesa::query()->with('jabatan')->orderBy('urutan')->orderBy('id')->get();
    }

    /** @return array<string, mixed> */
    private function larik(PerangkatDesa $p): array
    {
        return [
            'id' => $p->id,
            'nama' => $p->nama,
            'jabatan' => $p->jabatan->nama,
            'tampil' => $p->tampil,
            'foto_url' => UnggahBerkas::urlGambar($p->foto_path),
        ];
    }
}
