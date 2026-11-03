<?php

namespace App\Http\Controllers;

use App\Enums\StatusTerbit;
use App\Http\Requests\Admin\SimpanAgendaRequest;
use App\Models\Agenda;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Kelola Agenda. Rute dilindungi middleware `modul:berita`.
 * Hanya agenda berstatus terbit dan bertanggal mendatang yang tampil di situs publik.
 */
class AdminAgendaController extends Controller
{
    private const PER_HALAMAN = 5;

    public function index(Request $request): View
    {
        $waktu = in_array($request->query('waktu'), ['lewat', 'semua'], true) ? $request->query('waktu') : 'mendatang';
        $status = in_array($request->query('status'), ['terbit', 'draf'], true) ? $request->query('status') : 'semua';
        $kata = mb_substr(trim((string) $request->query('q', '')), 0, 100);

        $agenda = Agenda::query()
            ->when($waktu === 'mendatang', fn ($q) => $q->mendatang())
            ->when($waktu === 'lewat', fn ($q) => $q->lewat())
            ->when($status !== 'semua', fn ($q) => $q->where('status', $status))
            ->cari(['judul'], $kata)
            ->orderBy('tanggal')->orderBy('waktu')->orderBy('id')
            ->paginate(self::PER_HALAMAN)
            ->withQueryString()
            ->through(fn (Agenda $a) => $this->larik($a));

        $ubah = $request->filled('ubah') ? Agenda::query()->find($request->query('ubah')) : null;

        return view('pages.admin.agenda-index', [
            'agenda' => $agenda,
            'item' => $ubah ? $this->larik($ubah) : null,
            'ada' => Agenda::query()->exists(),
            'disaring' => $waktu !== 'mendatang' || $status !== 'semua' || $kata !== '',
            'hariIni' => Carbon::today(),
        ]);
    }

    public function store(SimpanAgendaRequest $request): RedirectResponse
    {
        $this->isi(new Agenda, $request)->save();

        return redirect()->route('admin.agenda.index', ['waktu' => 'semua'])->with('toast', 'Perubahan berhasil disimpan.');
    }

    public function update(SimpanAgendaRequest $request, int $id): RedirectResponse
    {
        $this->isi(Agenda::query()->findOrFail($id), $request)->save();

        return redirect()->route('admin.agenda.index', ['waktu' => 'semua'])->with('toast', 'Perubahan berhasil disimpan.');
    }

    public function destroy(int $id): RedirectResponse
    {
        Agenda::query()->findOrFail($id)->delete();

        return redirect()->route('admin.agenda.index', ['waktu' => 'semua'])->with('toast', 'Agenda berhasil dihapus.');
    }

    private function isi(Agenda $agenda, SimpanAgendaRequest $request): Agenda
    {
        $agenda->judul = $request->string('title')->toString();
        $agenda->tanggal = $request->input('tanggal');
        $agenda->waktu = $request->filled('waktu') ? $request->input('waktu') : null;
        $agenda->lokasi = $request->filled('lokasi') ? $request->string('lokasi')->toString() : null;
        $agenda->deskripsi = $request->filled('deskripsi') ? $request->string('deskripsi')->toString() : null;
        // Tombol yang ditekan menentukan status akhir.
        $agenda->status = StatusTerbit::from($request->input('aksi') ?: $request->input('status'));

        return $agenda;
    }

    /**
     * Bentuk yang dipakai tampilan admin.
     *
     * @return array<string, mixed>
     */
    private function larik(Agenda $a): array
    {
        $waktu = $a->waktu ? substr((string) $a->waktu, 0, 5) : null;

        return [
            'id' => $a->id,
            'title' => $a->judul,
            'tanggal' => $a->tanggal->toDateString(),
            'waktu' => $waktu,
            'time' => $waktu ? str_replace(':', '.', $waktu).' WIB' : 'Waktu menyusul',
            'location' => $a->lokasi,
            'status' => $a->status->value,
            'deskripsi' => $a->deskripsi,
        ];
    }
}
