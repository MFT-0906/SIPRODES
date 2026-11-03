<?php

namespace App\Http\Controllers;

use App\Enums\StatusTerbit;
use App\Http\Requests\Admin\SimpanBeritaRequest;
use App\Models\Berita;
use App\Support\UnggahBerkas;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Kelola Berita. Rute dilindungi middleware `modul:berita`. Status `terbit` hanya bisa ditetapkan
 * akun yang berhak atas modul ini; publik hanya membaca berita terbit.
 */
class AdminBeritaController extends Controller
{
    private const PER_HALAMAN = 5;

    private const KATEGORI_BAWAAN = ['Kegiatan', 'Pertanian', 'Pemerintahan', 'Pengumuman'];

    public function index(Request $request): View
    {
        $kategori = $this->kategori();
        $status = in_array($request->query('status'), ['terbit', 'draf'], true) ? $request->query('status') : 'semua';
        $kategoriDipilih = in_array($request->query('kategori'), $kategori, true) ? $request->query('kategori') : null;
        $kata = mb_substr(trim((string) $request->query('q', '')), 0, 100);

        $berita = Berita::query()
            ->when($status !== 'semua', fn ($q) => $q->where('status', $status))
            ->when($kategoriDipilih, fn ($q) => $q->where('kategori', $kategoriDipilih))
            ->cari(['judul'], $kata)
            ->orderByDesc('tanggal')->orderByDesc('id')
            ->paginate(self::PER_HALAMAN)
            ->withQueryString()
            ->through(fn (Berita $b) => $this->larik($b));

        return view('pages.admin.berita-index', [
            'berita' => $berita,
            'kategori' => collect($kategori),
            'ada' => Berita::query()->exists(),
            'disaring' => $status !== 'semua' || $kategoriDipilih !== null || $kata !== '',
        ]);
    }

    public function create(): View
    {
        return view('pages.admin.berita-form', ['item' => null, 'kategori' => $this->kategori()]);
    }

    public function store(SimpanBeritaRequest $request): RedirectResponse
    {
        $berita = DB::transaction(function () use ($request) {
            $berita = new Berita(['slug' => $this->slugUnik($request->string('title')->toString())]);
            $berita->penulis_id = $request->user()->id;
            $this->isi($berita, $request);
            $berita->save();

            return $berita;
        });

        return redirect()->route('admin.berita.index')->with('toast', 'Perubahan berhasil disimpan.');
    }

    public function edit(string $slug): View
    {
        return view('pages.admin.berita-form', [
            'item' => $this->larik(Berita::query()->where('slug', $slug)->firstOrFail()),
            'kategori' => $this->kategori(),
        ]);
    }

    public function update(SimpanBeritaRequest $request, string $slug): RedirectResponse
    {
        $berita = Berita::query()->where('slug', $slug)->firstOrFail();
        $gambarLama = $berita->gambar_path;

        // Alt tetap wajib selama gambar lama dipertahankan.
        if ($gambarLama && ! $request->hasFile('gambar') && ! $request->boolean('hapus_gambar') && blank($request->input('alt')) && blank($berita->gambar_alt)) {
            return back()->withInput()->withErrors(['alt' => 'Teks alternatif wajib diisi bila gambar utama dipilih.']);
        }

        DB::transaction(function () use ($berita, $request) {
            $this->isi($berita, $request);
            $berita->save();
        });

        if ($gambarLama && $gambarLama !== $berita->gambar_path) {
            UnggahBerkas::hapusGambar($gambarLama);
        }

        return redirect()->route('admin.berita.index')->with('toast', 'Perubahan berhasil disimpan.');
    }

    public function destroy(string $slug): RedirectResponse
    {
        $berita = Berita::query()->where('slug', $slug)->firstOrFail();
        $gambar = $berita->gambar_path;

        $berita->delete();
        UnggahBerkas::hapusGambar($gambar);

        return redirect()->route('admin.berita.index')->with('toast', 'Berita berhasil dihapus.');
    }

    /**
     * Memetakan isian formulir ke kolom. Slug tidak berubah saat judul diubah (tautan lama tetap berlaku).
     */
    private function isi(Berita $berita, SimpanBeritaRequest $request): void
    {
        // Tombol yang ditekan (Simpan sebagai Draf / Simpan & Terbitkan) menentukan status akhir.
        $status = StatusTerbit::from($request->input('aksi') ?: $request->input('status'));

        $berita->judul = $request->string('title')->toString();
        $berita->ringkasan = $request->string('excerpt')->toString();
        $berita->isi = $request->string('isi_teks')->toString();
        $berita->tanggal = $request->input('tanggal');
        $berita->kategori = $request->string('kategori')->toString();
        $berita->status = $status;
        $berita->diterbitkan_at = $status === StatusTerbit::Terbit ? ($berita->diterbitkan_at ?? now()) : null;

        if ($request->hasFile('gambar')) {
            $berita->gambar_path = UnggahBerkas::simpanGambar($request->file('gambar'), 'berita');
            $berita->gambar_alt = $request->string('alt')->toString();
        } elseif ($request->boolean('hapus_gambar')) {
            $berita->gambar_path = null;
            $berita->gambar_alt = null;
        } elseif ($berita->gambar_path && $request->filled('alt')) {
            $berita->gambar_alt = $request->string('alt')->toString();
        }
    }

    private function slugUnik(string $judul): string
    {
        $dasar = Str::limit(Str::slug($judul), 120, '') ?: 'berita';
        $slug = $dasar;

        for ($n = 2; Berita::query()->where('slug', $slug)->exists(); $n++) {
            $slug = $dasar.'-'.$n;
        }

        return $slug;
    }

    /** @return list<string> */
    private function kategori(): array
    {
        return Berita::query()->distinct()->pluck('kategori')->merge(self::KATEGORI_BAWAAN)->unique()->sort()->values()->all();
    }

    /**
     * Bentuk yang dipakai tampilan admin.
     *
     * @return array<string, mixed>
     */
    private function larik(Berita $b): array
    {
        return [
            'slug' => $b->slug,
            'title' => $b->judul,
            'excerpt' => $b->ringkasan ?? '',
            'isi_teks' => $b->isi,
            'category' => $b->kategori,
            'tanggal' => $b->tanggal->toDateString(),
            'status' => $b->status->value,
            'alt' => $b->gambar_alt,
            'gambar_url' => UnggahBerkas::urlGambar($b->gambar_path),
        ];
    }
}
