<?php

namespace App\Http\Controllers;

use App\Enums\StatusTerbit;
use App\Http\Requests\Admin\SimpanKelompokTaniRequest;
use App\Http\Requests\Admin\SimpanKomoditasRequest;
use App\Http\Requests\Admin\SimpanUmkmRequest;
use App\Models\Dusun;
use App\Models\KelompokTani;
use App\Models\Komoditas;
use App\Models\Umkm;
use App\Support\Format;
use App\Support\UnggahBerkas;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Admin Potensi: komoditas (Potensi Pertanian), Kelompok Tani, dan UMKM. Rute dilindungi middleware `modul:potensi`.
 * Kontak kelompok tani, WhatsApp, dan foto UMKM hanya tampil di publik bila persetujuan dicentang.
 */
class AdminPotensiController extends Controller
{
    // ---- Potensi Pertanian (komoditas) ------------------------------------------------------

    public function pertanian(Request $request): View
    {
        $status = in_array($request->query('status'), ['terbit', 'draf'], true) ? $request->query('status') : 'semua';
        $kata = mb_substr(trim((string) $request->query('q', '')), 0, 100);

        $komoditas = Komoditas::query()
            ->when($status !== 'semua', fn ($q) => $q->where('status', $status))
            ->cari(['nama'], $kata)
            ->orderBy('nama')->orderBy('id')->get();

        $ubah = $request->filled('ubah') ? Komoditas::query()->find($request->query('ubah')) : null;

        return view('pages.admin.pertanian', [
            'komoditas' => $komoditas->map(fn (Komoditas $k) => $this->larikKomoditas($k)),
            'kelompok' => KelompokTani::query()->with(['komoditas', 'dusun'])->orderBy('nama')->get()
                ->map(fn (KelompokTani $g) => $this->larikKelompok($g))->all(),
            'item' => $ubah ? $this->larikKomoditas($ubah) : null,
            'tambah' => $request->boolean('tambah'),
            'disaring' => $status !== 'semua' || $kata !== '',
            'ada' => Komoditas::query()->exists(),
        ]);
    }

    public function komoditasSimpan(SimpanKomoditasRequest $request, ?int $id = null): RedirectResponse
    {
        $komoditas = $id === null ? new Komoditas : Komoditas::query()->findOrFail($id);
        $fotoLama = $komoditas->foto_path;

        $komoditas->nama = $request->string('nama')->toString();
        $komoditas->musim_panen = $request->filled('musim_panen') ? $request->string('musim_panen')->toString() : null;
        $komoditas->deskripsi = $request->filled('deskripsi') ? $request->string('deskripsi')->toString() : null;
        $komoditas->status = StatusTerbit::from($request->string('status')->toString());
        $komoditas->unggulan = $request->boolean('unggulan');
        $komoditas->contoh = false;
        $this->isiFoto($komoditas, $request, 'komoditas');
        $komoditas->save();

        if ($fotoLama && $fotoLama !== $komoditas->foto_path) {
            UnggahBerkas::hapusGambar($fotoLama);
        }

        return redirect()->route('admin.pertanian')->with('toast', 'Perubahan berhasil disimpan.');
    }

    public function komoditasHapus(int $id): RedirectResponse
    {
        $komoditas = Komoditas::query()->findOrFail($id);
        $foto = $komoditas->foto_path;
        $komoditas->delete();
        UnggahBerkas::hapusGambar($foto);

        return redirect()->route('admin.pertanian')->with('toast', 'Komoditas berhasil dihapus.');
    }

    // ---- Kelompok Tani ------------------------------------------------------------------------

    public function kelompok(Request $request): View
    {
        $kata = mb_substr(trim((string) $request->query('q', '')), 0, 100);
        $daftar = KelompokTani::query()->with(['komoditas', 'dusun'])->cari(['nama', 'ketua'], $kata)->orderBy('nama')->get();
        $ubah = $request->filled('ubah') ? KelompokTani::query()->with(['komoditas', 'dusun'])->find($request->query('ubah')) : null;

        return view('pages.admin.kelompok-tani', [
            'daftar' => $daftar->map(fn (KelompokTani $g) => $this->larikKelompok($g)),
            'item' => $ubah ? $this->larikKelompok($ubah) : null,
            'komoditas' => Komoditas::query()->orderBy('nama')->pluck('nama', 'id')->all(),
            'dusun' => Dusun::query()->orderBy('urutan')->pluck('nama', 'id')->all(),
        ]);
    }

    public function kelompokSimpan(SimpanKelompokTaniRequest $request, ?int $id = null): RedirectResponse
    {
        $kelompok = $id === null ? new KelompokTani : KelompokTani::query()->findOrFail($id);

        $kelompok->nama = $request->string('nama')->toString();
        $kelompok->komoditas_id = $request->integer('komoditas');
        $kelompok->dusun_id = $request->integer('dusun');
        $kelompok->ketua = $request->filled('ketua') ? $request->string('ketua')->toString() : null;
        $kelompok->kontak = $request->filled('kontak') ? $request->string('kontak')->toString() : null;
        $kelompok->kontak_disetujui = $request->boolean('disetujui');
        $kelompok->contoh = false;
        $kelompok->save();

        return redirect()->route('admin.kelompok-tani')->with('toast', 'Perubahan berhasil disimpan.');
    }

    public function kelompokHapus(int $id): RedirectResponse
    {
        KelompokTani::query()->findOrFail($id)->delete();

        return redirect()->route('admin.kelompok-tani')->with('toast', 'Kelompok tani berhasil dihapus.');
    }

    // ---- UMKM ---------------------------------------------------------------------------------

    public function umkm(Request $request): View
    {
        $kata = mb_substr(trim((string) $request->query('q', '')), 0, 100);
        $daftar = Umkm::query()->cari(['nama', 'produk'], $kata)->orderBy('nama')->orderBy('id')->get();
        $ubah = $request->filled('ubah') ? Umkm::query()->find($request->query('ubah')) : null;

        return view('pages.admin.umkm', [
            'daftar' => $daftar->map(fn (Umkm $u) => $this->larikUmkm($u)),
            'item' => $ubah ? $this->larikUmkm($ubah) : null,
            'tambah' => $request->boolean('tambah'),
        ]);
    }

    public function umkmSimpan(SimpanUmkmRequest $request, ?int $id = null): RedirectResponse
    {
        $umkm = $id === null ? new Umkm : Umkm::query()->findOrFail($id);
        $fotoLama = $umkm->foto_path;

        $umkm->nama = $request->string('nama')->toString();
        $umkm->produk = $request->string('produk')->toString();
        $umkm->deskripsi = $request->filled('deskripsi') ? $request->string('deskripsi')->toString() : null;
        $umkm->whatsapp = Format::nomorWa($request->input('whatsapp'));
        $umkm->whatsapp_disetujui = $request->boolean('disetujui');
        $umkm->status = StatusTerbit::from($request->input('status') ?: ($umkm->exists ? $umkm->status->value : 'terbit'));
        $umkm->contoh = false;
        $this->isiFoto($umkm, $request, 'umkm');
        $umkm->save();

        if ($fotoLama && $fotoLama !== $umkm->foto_path) {
            UnggahBerkas::hapusGambar($fotoLama);
        }

        return redirect()->route('admin.umkm')->with('toast', 'Perubahan berhasil disimpan.');
    }

    public function umkmHapus(int $id): RedirectResponse
    {
        $umkm = Umkm::query()->findOrFail($id);
        $foto = $umkm->foto_path;
        $umkm->delete();
        UnggahBerkas::hapusGambar($foto);

        return redirect()->route('admin.umkm')->with('toast', 'UMKM berhasil dihapus.');
    }

    // ---- Pembantu -----------------------------------------------------------------------------

    /**
     * Foto + alt untuk model dengan kolom foto_path/foto_alt. Alt tetap wajib selama foto dipertahankan.
     */
    private function isiFoto(Komoditas|Umkm $model, Request $request, string $folder): void
    {
        if ($request->hasFile('foto')) {
            $model->foto_path = UnggahBerkas::simpanGambar($request->file('foto'), $folder, 'foto');
            $model->foto_alt = $request->string('alt')->toString();
        } elseif ($request->boolean('hapus_foto')) {
            $model->foto_path = null;
            $model->foto_alt = null;
        } elseif ($model->foto_path && $request->filled('alt')) {
            $model->foto_alt = $request->string('alt')->toString();
        }
    }

    /** @return array<string, mixed> */
    private function larikKomoditas(Komoditas $k): array
    {
        return [
            'id' => $k->id, 'nama' => $k->nama, 'musim_panen' => $k->musim_panen, 'status' => $k->status->value,
            'deskripsi' => $k->deskripsi, 'unggulan' => $k->unggulan, 'alt' => $k->foto_alt,
            'foto_url' => UnggahBerkas::urlGambar($k->foto_path),
        ];
    }

    /** @return array<string, mixed> */
    private function larikKelompok(KelompokTani $g): array
    {
        return [
            'id' => $g->id, 'nama' => $g->nama, 'komoditas' => $g->komoditas?->nama ?? '—', 'komoditas_id' => $g->komoditas_id,
            'dusun' => $g->dusun?->nama, 'dusun_id' => $g->dusun_id, 'ketua' => $g->ketua, 'kontak' => $g->kontak,
            'disetujui' => $g->kontak_disetujui,
        ];
    }

    /** @return array<string, mixed> */
    private function larikUmkm(Umkm $u): array
    {
        return [
            'id' => $u->id, 'nama' => $u->nama, 'produk' => $u->produk, 'deskripsi' => $u->deskripsi, 'whatsapp' => $u->whatsapp,
            'disetujui' => $u->whatsapp_disetujui, 'status' => $u->status->value, 'alt' => $u->foto_alt,
            'foto_url' => UnggahBerkas::urlGambar($u->foto_path),
        ];
    }
}
