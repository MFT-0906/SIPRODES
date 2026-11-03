<?php

namespace App\Http\Controllers;

use App\Enums\Modul;
use App\Enums\StatusAspirasi;
use App\Enums\StatusTerbit;
use App\Models\Agenda;
use App\Models\Aspirasi;
use App\Models\Berita;
use App\Models\KelompokTani;
use App\Models\Komoditas;
use App\Models\Umkm;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

/**
 * Ringkasan dashboard. Semua angka dihitung dari database, dan setiap bagian hanya dihitung
 * bila akun berhak atas modulnya (Staf tidak melihat data modul yang tidak ditugaskan).
 */
class AdminController extends Controller
{
    public function dashboard(): View
    {
        $bolehBerita = Gate::allows('akses-modul', Modul::Berita);
        $bolehPotensi = Gate::allows('akses-modul', Modul::Potensi);
        $bolehAspirasi = Gate::allows('akses-modul', Modul::Aspirasi);

        return view('pages.admin.dashboard', [
            'terbit' => $bolehBerita ? Berita::query()->terbit()->count() : 0,
            'draf' => $bolehBerita ? Berita::query()->draf()->count() : 0,
            'umkm' => $bolehPotensi ? Umkm::query()->terbit()->count() : 0,
            'komoditas' => $bolehPotensi ? Komoditas::query()->terbit()->count() : 0,
            'kelompokTani' => $bolehPotensi ? KelompokTani::query()->count() : 0,
            'aspirasiBaru' => $bolehAspirasi ? Aspirasi::query()->where('status', StatusAspirasi::Baru)->count() : 0,
            // Cuplikan saja; NIK tidak pernah dipilih dari database.
            'aspirasiTerbaru' => $bolehAspirasi
                ? Aspirasi::query()->select(['id', 'nama', 'pesan', 'status', 'created_at'])->latest()->limit(3)->get()
                    ->map(fn (Aspirasi $a) => [
                        'pengirim' => $a->nama ?? 'Tanpa nama',
                        'cuplikan' => Str::limit($a->pesan, 48),
                        'status' => $a->status->value,
                        'waktu' => $a->created_at->locale('id')->diffForHumans(),
                    ])->all()
                : [],
            'konten' => $bolehBerita ? $this->drafTerakhir() : [],
        ]);
    }

    /**
     * Berita dan agenda draf terakhir, diurutkan menurut waktu diubah.
     *
     * @return list<array{judul: string, jenis: string, diubah: string, href: string}>
     */
    private function drafTerakhir(): array
    {
        $berita = Berita::query()->draf()->latest('updated_at')->limit(5)->get()
            ->map(fn (Berita $b) => [
                'judul' => $b->judul, 'jenis' => 'Berita', 'diubah' => $b->updated_at->toDateString(), 'urut' => $b->updated_at,
                'href' => route('admin.berita.edit', $b->slug),
            ]);

        $agenda = Agenda::query()->where('status', StatusTerbit::Draf)->latest('updated_at')->limit(5)->get()
            ->map(fn (Agenda $a) => [
                'judul' => $a->judul, 'jenis' => 'Agenda', 'diubah' => $a->updated_at->toDateString(), 'urut' => $a->updated_at,
                'href' => route('admin.agenda.index', ['ubah' => $a->id, 'waktu' => 'semua']).'#form-agenda',
            ]);

        return $berita->concat($agenda)->sortByDesc('urut')->take(5)
            ->map(fn (array $k) => collect($k)->except('urut')->all())->values()->all();
    }
}
