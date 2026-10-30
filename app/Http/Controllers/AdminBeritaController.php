<?php

namespace App\Http\Controllers;

use App\Support\DemoStore;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class AdminBeritaController extends Controller
{
    // TODO(backend): middleware `auth`, pengecekan izin, penyimpanan ke database, dan penyimpanan gambar.
    // Hanya berita berstatus terbit yang boleh tampil di situs publik (ditegakkan di sisi publik).

    private const PER_HALAMAN = 5;

    private const KATEGORI_BAWAAN = ['Kegiatan', 'Pertanian', 'Pemerintahan', 'Pengumuman'];

    public function index(Request $request): View
    {
        // Contoh tabel kosong, hanya di lokal: /admin/berita?demo=kosong
        $semua = app()->environment('local') && $request->query('demo') === 'kosong'
            ? collect()
            : collect(DemoStore::berita());

        $kategori = $semua->pluck('category')->merge(self::KATEGORI_BAWAAN)->unique()->sort()->values();
        $status = in_array($request->query('status'), ['terbit', 'draf'], true) ? $request->query('status') : 'semua';
        $kategoriDipilih = $kategori->contains($request->query('kategori')) ? $request->query('kategori') : null;
        $kata = trim((string) $request->query('q', ''));

        $hasil = $semua
            ->when($status !== 'semua', fn ($c) => $c->where('status', $status))
            ->when($kategoriDipilih, fn ($c) => $c->where('category', $kategoriDipilih))
            ->when($kata !== '', fn ($c) => $c->filter(fn (array $b) => str_contains(mb_strtolower($b['title']), mb_strtolower($kata))))
            ->sortByDesc('tanggal')
            ->values();

        $halaman = max(1, (int) $request->query('page', 1));
        $berita = new LengthAwarePaginator(
            $hasil->forPage($halaman, self::PER_HALAMAN)->values(),
            $hasil->count(),
            self::PER_HALAMAN,
            $halaman,
            ['path' => $request->url(), 'query' => $request->except('page')],
        );

        return view('pages.admin.berita-index', [
            'berita' => $berita,
            'kategori' => $kategori,
            'ada' => $semua->isNotEmpty(),
            'disaring' => $status !== 'semua' || $kategoriDipilih !== null || $kata !== '',
        ]);
    }

    public function create(): View
    {
        return view('pages.admin.berita-form', ['item' => null, 'kategori' => $this->kategori()]);
    }

    public function store(Request $request): RedirectResponse
    {
        DemoStore::simpanBerita($this->data($request));

        return redirect()->route('admin.berita.index')->with('toast', 'Perubahan berhasil disimpan.');
    }

    public function edit(string $slug): View
    {
        $item = DemoStore::beritaSlug($slug);
        abort_if($item === null, 404);

        return view('pages.admin.berita-form', ['item' => $item, 'kategori' => $this->kategori()]);
    }

    public function update(Request $request, string $slug): RedirectResponse
    {
        abort_if(DemoStore::beritaSlug($slug) === null, 404);

        DemoStore::simpanBerita($this->data($request), $slug);

        return redirect()->route('admin.berita.index')->with('toast', 'Perubahan berhasil disimpan.');
    }

    public function destroy(string $slug): RedirectResponse
    {
        abort_if(DemoStore::beritaSlug($slug) === null, 404);

        DemoStore::hapusBerita($slug);

        return redirect()->route('admin.berita.index')->with('toast', 'Berita berhasil dihapus.');
    }

    /** @return list<string> */
    private function kategori(): array
    {
        return collect(DemoStore::berita())->pluck('category')->merge(self::KATEGORI_BAWAAN)->unique()->sort()->values()->all();
    }

    /** @return array<string, mixed> */
    private function data(Request $request): array
    {
        $valid = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'excerpt' => ['required', 'string', 'max:300'],
            'isi_teks' => ['required', 'string', 'max:20000'],
            'tanggal' => ['required', 'date'],
            'kategori' => ['required', 'string', 'max:60'],
            // TODO(backend): tetapkan batas ukuran gambar sesuai kebijakan desa, periksa isi berkas, dan simpan.
            'gambar' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp'],
            'alt' => ['required_with:gambar', 'nullable', 'string', 'max:200'],
            'status' => ['required', 'in:draf,terbit'],
            'aksi' => ['nullable', 'in:draf,terbit'],
        ], [
            'required' => ':attribute wajib diisi.',
            'max' => ':attribute terlalu panjang (maksimal :max karakter).',
            'date' => ':attribute belum berupa tanggal yang benar.',
            'mimes' => 'Gambar harus berformat JPG, PNG, atau WebP.',
            'required_with' => ':attribute wajib diisi bila gambar utama dipilih.',
            'in' => ':attribute tidak dikenal.',
        ], [
            'title' => 'Judul berita',
            'excerpt' => 'Ringkasan',
            'isi_teks' => 'Isi berita',
            'tanggal' => 'Tanggal',
            'kategori' => 'Kategori',
            'alt' => 'Teks alternatif',
            'status' => 'Status',
        ]);

        return [
            'title' => $valid['title'],
            'excerpt' => $valid['excerpt'],
            'isi_teks' => $valid['isi_teks'],
            'tanggal' => $valid['tanggal'],
            'category' => $valid['kategori'],
            'alt' => $valid['alt'] ?? null,
            // Tombol yang ditekan (Simpan sebagai Draf / Simpan & Terbitkan) menentukan status akhir.
            'status' => $valid['aksi'] ?? $valid['status'],
        ];
    }
}
