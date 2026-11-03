<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use App\Models\ApbdesDokumen;
use App\Models\ApbdesPeriode;
use App\Models\Berita;
use App\Models\Umkm;
use App\Support\KontenPublik;
use App\Support\UnggahBerkas;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Halaman publik. Semua data dibaca dari database dan hanya konten berstatus `terbit`
 * yang boleh keluar (lihat KontenPublik dan scope terbit()).
 */
class PublicPageController extends Controller
{
    private const BERITA_PER_HALAMAN = 4;

    /**
     * Diambil per permintaan (scoped) agar data profil tidak basi pada instance controller yang dipakai ulang.
     */
    private function konten(): KontenPublik
    {
        return app(KontenPublik::class);
    }

    public function home(): View
    {
        return view('pages.public.beranda', [
            'content' => $this->konten()->beranda(),
            'statistik' => $this->konten()->statistik(),
            'identitas' => $this->konten()->identitas(),
        ]);
    }

    public function profil(): View
    {
        return view('pages.public.profil', [
            'content' => $this->konten()->profil(),
            'identitas' => $this->konten()->identitas(),
        ]);
    }

    public function apbdes(Request $request): View
    {
        try {
            $tersedia = $this->konten()->tahunApbdes();

            if ($tersedia === []) {
                return view('pages.public.apbdes-kosong');
            }

            // Tahun yang tidak dikenal (atau draf) kembali ke tahun terbaru, bukan error.
            $tahun = (int) $request->query('tahun', $tersedia[0]);
            if (! in_array($tahun, $tersedia, true)) {
                $tahun = $tersedia[0];
            }

            return view('pages.public.apbdes', [
                'pengantar' => 'Ringkasan Anggaran Pendapatan dan Belanja Desa (APBDes) Marga Agung beserta dokumen resminya. Seluruh angka bersumber dari dokumen APBDes yang telah ditetapkan.',
                'tahunTersedia' => $tersedia,
                'tahun' => $tahun,
                'data' => $this->konten()->apbdes($tahun),
            ]);
        } catch (QueryException $e) {
            report($e);

            return view('pages.public.apbdes-gagal');
        }
    }

    /**
     * Unduhan PDF APBDes dari disk privat. Hanya dokumen milik periode yang terbit; selain itu 404
     * (draf tidak boleh bocor lewat tebakan id).
     */
    public function unduhDokumen(ApbdesDokumen $dokumen): StreamedResponse
    {
        abort_unless(
            filled($dokumen->path)
            && ApbdesPeriode::query()->terbit()->whereKey($dokumen->periode_id)->exists()
            && Storage::disk('local')->exists($dokumen->path),
            404,
        );

        return UnggahBerkas::unduhPdf($dokumen->path, $dokumen->judul);
    }

    public function potensi(Request $request): View
    {
        $kata = mb_substr(trim((string) $request->query('q', '')), 0, 100);
        $content = $this->konten()->potensi($kata);

        $umkm = Umkm::query()->terbit()
            ->cari(['nama', 'produk', 'deskripsi'], $kata)
            ->orderBy('nama')
            ->paginate($content['umkm']['per_halaman'])
            ->withQueryString()
            ->fragment('umkm')
            ->through(fn (Umkm $u) => $this->konten()->umkm($u));

        return view('pages.public.potensi', [
            'content' => $content,
            'umkm' => $umkm,
            'kata' => $kata,
            'tabAwal' => in_array($request->query('tab'), ['pertanian', 'umkm'], true) ? $request->query('tab') : 'pertanian',
        ]);
    }

    public function berita(Request $request): View
    {
        $kategori = Berita::query()->terbit()->distinct()->orderBy('kategori')->pluck('kategori');

        $aktif = $request->query('kategori');
        $aktif = is_string($aktif) && $kategori->contains($aktif) ? $aktif : null;
        $kata = mb_substr(trim((string) $request->query('q', '')), 0, 100);

        $berita = Berita::query()->terbit()
            ->when($aktif, fn ($q) => $q->where('kategori', $aktif))
            ->cari(['judul', 'ringkasan'], $kata)
            ->orderByDesc('tanggal')->orderByDesc('id')
            ->paginate(self::BERITA_PER_HALAMAN)
            ->withQueryString()
            ->through(fn (Berita $b) => KontenPublik::berita($b));

        return view('pages.public.berita', [
            'berita' => $berita,
            'kategori' => $kategori,
            'aktif' => $aktif,
            'kata' => $kata,
            'adaBerita' => $kategori->isNotEmpty(),
            'agendaMendatang' => Agenda::query()->terbit()->mendatang()->orderBy('tanggal')->orderBy('waktu')->get()
                ->map(fn (Agenda $a) => KontenPublik::agenda($a))->all(),
            'agendaLewat' => Agenda::query()->terbit()->lewat()->orderByDesc('tanggal')->limit(10)->get()
                ->map(fn (Agenda $a) => KontenPublik::agenda($a))->all(),
        ]);
    }

    public function beritaShow(string $slug): View
    {
        // Draf atau slug yang tidak ada tidak boleh dapat dibuka publik (404).
        $artikel = KontenPublik::berita(Berita::query()->terbit()->where('slug', $slug)->firstOrFail());

        $kata = count(preg_split('/\s+/u', $artikel['isi_polos'], -1, PREG_SPLIT_NO_EMPTY));

        return view('pages.public.berita-detail', [
            'artikel' => $artikel,
            'menitBaca' => max(1, (int) ceil($kata / 200)),
            'lainnya' => Berita::query()->terbit()->where('slug', '!=', $slug)
                ->orderByDesc('tanggal')->orderByDesc('id')->limit(3)->get()
                ->map(fn (Berita $b) => KontenPublik::berita($b))->all(),
        ]);
    }

    public function kontak(): View
    {
        return view('pages.public.kontak', [
            'identitas' => $this->konten()->identitas(),
            'statistik' => collect($this->konten()->statistik())->keyBy('label'),
        ]);
    }
}
