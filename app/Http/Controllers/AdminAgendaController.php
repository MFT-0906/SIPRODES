<?php

namespace App\Http\Controllers;

use App\Support\DemoStore;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class AdminAgendaController extends Controller
{
    // TODO(backend): penyimpanan ke database.
    // Hanya agenda berstatus terbit dan bertanggal mendatang yang tampil di situs publik.

    private const PER_HALAMAN = 5;

    public function index(Request $request): View
    {
        $semua = collect(DemoStore::agenda());
        $hariIni = Carbon::today();

        $waktu = in_array($request->query('waktu'), ['lewat', 'semua'], true) ? $request->query('waktu') : 'mendatang';
        $status = in_array($request->query('status'), ['terbit', 'draf'], true) ? $request->query('status') : 'semua';
        $kata = trim((string) $request->query('q', ''));

        $hasil = $semua
            ->when($waktu === 'mendatang', fn ($c) => $c->filter(fn (array $a) => Carbon::parse($a['tanggal'])->gte($hariIni)))
            ->when($waktu === 'lewat', fn ($c) => $c->filter(fn (array $a) => Carbon::parse($a['tanggal'])->lt($hariIni)))
            ->when($status !== 'semua', fn ($c) => $c->where('status', $status))
            ->when($kata !== '', fn ($c) => $c->filter(fn (array $a) => str_contains(mb_strtolower($a['title']), mb_strtolower($kata))))
            ->sortBy('tanggal')
            ->values();

        $halaman = max(1, (int) $request->query('page', 1));
        $agenda = new LengthAwarePaginator(
            $hasil->forPage($halaman, self::PER_HALAMAN)->values(),
            $hasil->count(),
            self::PER_HALAMAN,
            $halaman,
            ['path' => $request->url(), 'query' => $request->except('page')],
        );

        $ubah = $request->query('ubah') ? DemoStore::agendaId((string) $request->query('ubah')) : null;

        return view('pages.admin.agenda-index', [
            'agenda' => $agenda,
            'item' => $ubah,
            'ada' => $semua->isNotEmpty(),
            'disaring' => $waktu !== 'mendatang' || $status !== 'semua' || $kata !== '',
            'hariIni' => $hariIni,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        DemoStore::simpanAgenda($this->data($request));

        return redirect()->route('admin.agenda.index', ['waktu' => 'semua'])->with('toast', 'Perubahan berhasil disimpan.');
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        abort_if(DemoStore::agendaId($id) === null, 404);

        DemoStore::simpanAgenda($this->data($request), $id);

        return redirect()->route('admin.agenda.index', ['waktu' => 'semua'])->with('toast', 'Perubahan berhasil disimpan.');
    }

    public function destroy(string $id): RedirectResponse
    {
        abort_if(DemoStore::agendaId($id) === null, 404);

        DemoStore::hapusAgenda($id);

        return redirect()->route('admin.agenda.index', ['waktu' => 'semua'])->with('toast', 'Agenda berhasil dihapus.');
    }

    /** @return array<string, mixed> */
    private function data(Request $request): array
    {
        $valid = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'tanggal' => ['required', 'date'],
            'waktu' => ['nullable', 'date_format:H:i'],
            'lokasi' => ['nullable', 'string', 'max:150'],
            'status' => ['required', 'in:draf,terbit'],
            'aksi' => ['nullable', 'in:draf,terbit'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
        ], [
            'required' => ':attribute wajib diisi.',
            'max' => ':attribute terlalu panjang (maksimal :max karakter).',
            'date' => ':attribute belum berupa tanggal yang benar.',
            'date_format' => ':attribute belum berupa jam yang benar.',
            'in' => ':attribute tidak dikenal.',
        ], [
            'title' => 'Judul agenda',
            'tanggal' => 'Tanggal',
            'waktu' => 'Waktu',
            'lokasi' => 'Lokasi',
            'status' => 'Status',
            'deskripsi' => 'Deskripsi',
        ]);

        return [
            'title' => $valid['title'],
            'tanggal' => $valid['tanggal'],
            'waktu' => $valid['waktu'] ?? null,
            'location' => $valid['lokasi'] ?? null,
            'status' => $valid['aksi'] ?? $valid['status'],
            'deskripsi' => $valid['deskripsi'] ?? null,
        ];
    }
}
