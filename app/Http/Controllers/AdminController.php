<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Support\DemoStore;
use Illuminate\Contracts\View\View;

class AdminController extends Controller
{
    // TODO(backend): angka ringkasan dari database (Fase 5).

    public function dashboard(): View
    {
        $berita = collect(DemoStore::berita());
        $agenda = collect(DemoStore::agenda());
        $aspirasi = collect(DemoStore::daftar('aspirasi'))->sortByDesc('diterima')->values();

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
            'aspirasiTerbaru' => $aspirasi->take(3)->map(fn (array $a) => [
                'pengirim' => $a['nama'],
                'cuplikan' => Str::limit($a['pesan'], 48),
                'status' => $a['status'],
                'waktu' => Carbon::parse($a['diterima'])->locale('id')->diffForHumans(),
            ])->all(),
            'konten' => $draf,
        ]);
    }
}
