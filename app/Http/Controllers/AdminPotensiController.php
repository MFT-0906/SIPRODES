<?php

namespace App\Http\Controllers;

use App\Support\DemoStore;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Admin Potensi: komoditas (Potensi Pertanian), Kelompok Tani, dan UMKM.
 */
class AdminPotensiController extends Controller
{
    // TODO(backend): penyimpanan foto dan data ke database.
    // Kontak kelompok tani, WhatsApp, dan foto UMKM hanya tampil di publik bila persetujuan dicentang.

    private const PESAN = [
        'required' => ':attribute wajib diisi.',
        'max' => ':attribute terlalu panjang (maksimal :max karakter).',
        'in' => ':attribute tidak dikenal.',
        'regex' => ':attribute belum berupa nomor telepon yang benar.',
        'mimes' => 'Foto harus berformat JPG, PNG, atau WebP.',
        'required_if' => ':attribute wajib diisi bila dicentang untuk publikasi.',
    ];

    private const TELEPON = ['nullable', 'regex:/^[0-9 +\-]{8,20}$/'];

    // ---- Potensi Pertanian (komoditas) ------------------------------------------------------

    public function pertanian(Request $request): View
    {
        $semua = collect(DemoStore::daftar('komoditas'));
        $status = in_array($request->query('status'), ['terbit', 'draf'], true) ? $request->query('status') : 'semua';
        $kata = trim((string) $request->query('q', ''));

        $komoditas = $semua
            ->when($status !== 'semua', fn ($c) => $c->where('status', $status))
            ->when($kata !== '', fn ($c) => $c->filter(fn (array $k) => str_contains(mb_strtolower($k['nama']), mb_strtolower($kata))))
            ->values();

        return view('pages.admin.pertanian', [
            'komoditas' => $komoditas,
            'kelompok' => DemoStore::daftar('kelompok'),
            'item' => DemoStore::item('komoditas', $request->query('ubah')),
            'tambah' => $request->boolean('tambah'),
            'disaring' => $status !== 'semua' || $kata !== '',
            'ada' => $semua->isNotEmpty(),
        ]);
    }

    public function komoditasSimpan(Request $request, ?string $id = null): RedirectResponse
    {
        abort_if($id !== null && DemoStore::item('komoditas', $id) === null, 404);

        $valid = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'musim_panen' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'in:draf,terbit'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
        ], self::PESAN, ['nama' => 'Nama komoditas', 'musim_panen' => 'Musim panen', 'status' => 'Status']);

        DemoStore::simpan('komoditas', [
            'nama' => $valid['nama'], 'musim_panen' => $valid['musim_panen'] ?? null,
            'status' => $valid['status'], 'deskripsi' => $valid['deskripsi'] ?? null,
        ], $id);

        return redirect()->route('admin.pertanian')->with('toast', 'Perubahan berhasil disimpan.');
    }

    public function komoditasHapus(string $id): RedirectResponse
    {
        abort_if(DemoStore::item('komoditas', $id) === null, 404);
        DemoStore::hapus('komoditas', $id);

        return redirect()->route('admin.pertanian')->with('toast', 'Komoditas berhasil dihapus.');
    }

    // ---- Kelompok Tani ------------------------------------------------------------------------

    public function kelompok(Request $request): View
    {
        $kata = trim((string) $request->query('q', ''));
        $daftar = collect(DemoStore::daftar('kelompok'))
            ->when($kata !== '', fn ($c) => $c->filter(fn (array $k) => str_contains(mb_strtolower($k['nama']), mb_strtolower($kata))))
            ->values();

        return view('pages.admin.kelompok-tani', [
            'daftar' => $daftar,
            'item' => DemoStore::item('kelompok', $request->query('ubah')),
            'komoditas' => collect(DemoStore::daftar('komoditas'))->pluck('nama')->all(),
            'dusun' => $this->dusun(),
        ]);
    }

    public function kelompokSimpan(Request $request, ?string $id = null): RedirectResponse
    {
        abort_if($id !== null && DemoStore::item('kelompok', $id) === null, 404);

        $valid = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'komoditas' => ['required', Rule::in(collect(DemoStore::daftar('komoditas'))->pluck('nama')->all())],
            'dusun' => ['required', Rule::in($this->dusun())],
            'ketua' => ['nullable', 'string', 'max:100'],
            'kontak' => ['required_if:disetujui,1', ...self::TELEPON],
            'disetujui' => ['nullable', 'boolean'],
        ], self::PESAN, ['nama' => 'Nama kelompok', 'komoditas' => 'Komoditas', 'dusun' => 'Dusun', 'kontak' => 'Nomor kontak', 'ketua' => 'Nama ketua']);

        DemoStore::simpan('kelompok', [
            'nama' => $valid['nama'], 'komoditas' => $valid['komoditas'], 'dusun' => $valid['dusun'],
            'ketua' => $valid['ketua'] ?? null, 'kontak' => $valid['kontak'] ?? null,
            'disetujui' => (bool) ($valid['disetujui'] ?? false),
        ], $id);

        return redirect()->route('admin.kelompok-tani')->with('toast', 'Perubahan berhasil disimpan.');
    }

    public function kelompokHapus(string $id): RedirectResponse
    {
        abort_if(DemoStore::item('kelompok', $id) === null, 404);
        DemoStore::hapus('kelompok', $id);

        return redirect()->route('admin.kelompok-tani')->with('toast', 'Kelompok tani berhasil dihapus.');
    }

    // ---- UMKM ---------------------------------------------------------------------------------

    public function umkm(Request $request): View
    {
        $kata = trim((string) $request->query('q', ''));
        $daftar = collect(DemoStore::daftar('umkm'))
            ->when($kata !== '', fn ($c) => $c->filter(fn (array $u) => str_contains(mb_strtolower($u['nama']), mb_strtolower($kata))))
            ->values();

        return view('pages.admin.umkm', [
            'daftar' => $daftar,
            'item' => DemoStore::item('umkm', $request->query('ubah')),
            'tambah' => $request->boolean('tambah'),
        ]);
    }

    public function umkmSimpan(Request $request, ?string $id = null): RedirectResponse
    {
        abort_if($id !== null && DemoStore::item('umkm', $id) === null, 404);

        $valid = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'produk' => ['required', 'string', 'max:150'],
            'deskripsi' => ['nullable', 'string', 'max:300'],
            'whatsapp' => self::TELEPON,
            'disetujui' => ['nullable', 'boolean'],
            // TODO(backend): periksa isi berkas, batas ukuran, dan simpan foto; tampil di publik hanya bila disetujui.
            'foto' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp'],
        ], self::PESAN, ['nama' => 'Nama usaha', 'produk' => 'Produk', 'deskripsi' => 'Deskripsi', 'whatsapp' => 'Nomor WhatsApp']);

        DemoStore::simpan('umkm', [
            'nama' => $valid['nama'], 'produk' => $valid['produk'], 'deskripsi' => $valid['deskripsi'] ?? null,
            'whatsapp' => $valid['whatsapp'] ?? null, 'disetujui' => (bool) ($valid['disetujui'] ?? false),
            'status' => DemoStore::item('umkm', $id)['status'] ?? 'terbit',
        ], $id);

        return redirect()->route('admin.umkm')->with('toast', 'Perubahan berhasil disimpan.');
    }

    public function umkmHapus(string $id): RedirectResponse
    {
        abort_if(DemoStore::item('umkm', $id) === null, 404);
        DemoStore::hapus('umkm', $id);

        return redirect()->route('admin.umkm')->with('toast', 'UMKM berhasil dihapus.');
    }

    /** @return list<string> */
    private function dusun(): array
    {
        // TODO(backend): daftar dusun resmi dari data master desa (jumlah dusun: 6, perlu verifikasi).
        return array_map(fn (int $n) => 'Dusun '.$n, range(1, 6));
    }
}
