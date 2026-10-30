<?php

namespace App\Http\Controllers;

use App\Support\DemoContent;
use App\Support\DemoStore;
use App\Support\Navigation;
use Illuminate\Contracts\View\View;

class AdminController extends Controller
{
    // TODO(backend): middleware `auth` dan pengecekan izin per peran dipasang oleh backend.

    public function dashboard(): View
    {
        $berita = collect(DemoStore::berita());
        $agenda = collect(DemoStore::agenda());
        $demo = DemoContent::admin();

        // Konten draf terakhir dari berita dan agenda, diurutkan menurut tanggal diubah.
        $draf = $berita->where('status', 'draf')
            ->map(fn (array $b) => [
                'judul' => $b['title'], 'jenis' => 'Berita', 'diubah' => $b['diubah'] ?? $b['tanggal'],
                'href' => route('admin.berita.edit', $b['slug']),
            ])
            ->concat($agenda->where('status', 'draf')->map(fn (array $a) => [
                'judul' => $a['title'], 'jenis' => 'Agenda', 'diubah' => $a['diubah'],
                'href' => route('admin.agenda.index', ['ubah' => $a['id'], 'waktu' => 'semua']).'#form-agenda',
            ]))
            ->sortByDesc('diubah')
            ->take(5)
            ->values()
            ->all();

        return view('pages.admin.dashboard', [
            'terbit' => $berita->where('status', 'terbit')->count(),
            'draf' => $berita->where('status', 'draf')->count(),
            'aspirasiBaru' => $demo['aspirasi_baru'],
            'aspirasiTerbaru' => $demo['aspirasi_terbaru'],
            'konten' => $draf,
        ]);
    }

    /** Halaman kerangka admin; isi tiap modul dibuat pada fase 10. */
    public function halaman(): View
    {
        foreach (Navigation::admin() as $grup) {
            foreach ($grup['items'] as $item) {
                if (request()->routeIs(...$item['match'])) {
                    return view('pages.admin.stub', ['judul' => $item['label']]);
                }
            }
        }

        abort(404);
    }
}
