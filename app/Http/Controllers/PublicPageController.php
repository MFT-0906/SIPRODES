<?php

namespace App\Http\Controllers;

use App\Support\DemoContent;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class PublicPageController extends Controller
{
    // TODO(backend): ganti DemoContent dengan data dari database.

    public function home(): View
    {
        return view('pages.public.beranda', [
            'content' => DemoContent::beranda(),
            'statistik' => DemoContent::statistik(),
            'identitas' => DemoContent::identitas(),
        ]);
    }

    public function profil(): View
    {
        return view('pages.public.profil', [
            'content' => DemoContent::profil(),
            'identitas' => DemoContent::identitas(),
        ]);
    }

    public function apbdes(Request $request): View
    {
        // Contoh keadaan gagal memuat, hanya di lokal: /transparansi-apbdes?demo=gagal
        if ($this->demo($request) === 'gagal') {
            return view('pages.public.apbdes-gagal');
        }

        $content = DemoContent::apbdes();
        $tersedia = array_keys($content['tahun']);

        // Tahun yang tidak dikenal kembali ke tahun bawaan, bukan error.
        $tahun = (int) $request->query('tahun', $content['tahun_default']);
        if (! in_array($tahun, $tersedia, true)) {
            $tahun = $content['tahun_default'];
        }

        return view('pages.public.apbdes', [
            'pengantar' => $content['pengantar'],
            'tahunTersedia' => $tersedia,
            'tahun' => $tahun,
            'data' => $content['tahun'][$tahun],
        ]);
    }

    public function potensi(Request $request): View
    {
        $content = DemoContent::potensi();

        $umkm = collect($content['umkm']['daftar']);
        $paginator = new LengthAwarePaginator(
            $umkm->forPage((int) $request->query('page', 1), $content['umkm']['per_halaman'])->values(),
            $umkm->count(),
            $content['umkm']['per_halaman'],
            (int) $request->query('page', 1),
            ['path' => $request->url(), 'query' => $request->except('page')],
        );

        return view('pages.public.potensi', [
            'content' => $content,
            'umkm' => $paginator,
        ]);
    }

    public function berita(Request $request): View
    {
        // Hanya berita berstatus terbit yang boleh tampil di situs publik.
        $demo = $this->demo($request);
        $terbit = $demo === 'kosong' ? collect() : $this->beritaTerbit();
        $kategori = $terbit->pluck('category')->unique()->sort()->values();

        $aktif = $request->query('kategori');
        if (! $kategori->contains($aktif)) {
            $aktif = null;
        }
        $kata = trim((string) $request->query('q', ''));

        $hasil = $terbit
            ->when($aktif, fn ($c) => $c->where('category', $aktif))
            ->when($kata !== '', fn ($c) => $c->filter(
                fn (array $b) => str_contains(mb_strtolower($b['title'].' '.$b['excerpt']), mb_strtolower($kata))
            ))
            ->values();

        $perHalaman = 4;
        $halaman = max(1, (int) $request->query('page', 1));
        $berita = new LengthAwarePaginator(
            $hasil->forPage($halaman, $perHalaman)->values(),
            $hasil->count(),
            $perHalaman,
            $halaman,
            ['path' => $request->url(), 'query' => $request->except('page')],
        );

        $hariIni = Carbon::today();
        $agenda = collect(DemoContent::agenda());

        return view('pages.public.berita', [
            'berita' => $berita,
            'kategori' => $kategori,
            'aktif' => $aktif,
            'kata' => $kata,
            'adaBerita' => $terbit->isNotEmpty(),
            'memuat' => $demo === 'memuat',
            'agendaMendatang' => $agenda->filter(fn ($a) => Carbon::parse($a['tanggal'])->gte($hariIni))->sortBy('tanggal')->values()->all(),
            'agendaLewat' => $agenda->filter(fn ($a) => Carbon::parse($a['tanggal'])->lt($hariIni))->sortByDesc('tanggal')->values()->all(),
        ]);
    }

    public function beritaShow(string $slug): View
    {
        $terbit = $this->beritaTerbit();
        $artikel = $terbit->firstWhere('slug', $slug);

        // Draf atau slug yang tidak ada tidak boleh dapat dibuka publik.
        abort_if($artikel === null, 404);

        $kata = str_word_count(strip_tags(collect($artikel['isi'])->pluck(1)->implode(' ')));

        return view('pages.public.berita-detail', [
            'artikel' => $artikel,
            'menitBaca' => max(1, (int) ceil($kata / 200)),
            'lainnya' => $terbit->where('slug', '!=', $slug)->take(3)->values()->all(),
        ]);
    }

    public function kontak(): View
    {
        return view('pages.public.kontak', [
            'identitas' => DemoContent::identitas(),
            'statistik' => collect(DemoContent::statistik())->keyBy('label'),
        ]);
    }

    /**
     * Keadaan contoh (?demo=kosong|memuat|gagal) untuk meninjau tampilan; hanya aktif di lingkungan lokal.
     */
    private function demo(Request $request): ?string
    {
        $demo = $request->query('demo');

        return app()->environment('local') && in_array($demo, ['kosong', 'memuat', 'gagal'], true) ? $demo : null;
    }

    /** @return \Illuminate\Support\Collection<int, array<string, mixed>> */
    private function beritaTerbit(): \Illuminate\Support\Collection
    {
        return collect(DemoContent::berita())->where('status', 'terbit')->sortByDesc('tanggal')->values();
    }
}
