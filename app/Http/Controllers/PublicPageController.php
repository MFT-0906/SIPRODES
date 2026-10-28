<?php

namespace App\Http\Controllers;

use App\Support\DemoContent;
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
}
