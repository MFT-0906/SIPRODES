<?php

namespace App\Http\Controllers;

use App\Enums\JenisAnggaran;
use App\Enums\StatusTerbit;
use App\Http\Requests\Admin\SimpanApbdesRequest;
use App\Models\ApbdesDokumen;
use App\Models\ApbdesPeriode;
use App\Support\Aktivitas;
use App\Support\UnggahBerkas;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Kelola APBDes: periode, rincian (total dihitung otomatis), dan dokumen PDF di disk privat.
 * Rute dilindungi middleware `modul:apbdes`.
 */
class AdminApbdesController extends Controller
{
    /** Uraian bawaan yang dipakai situs publik; admin dapat menambah atau mengubahnya. */
    private const URAIAN_BAWAAN = [
        'pendapatan' => ['Pendapatan Asli Desa', 'Dana Desa', 'Alokasi Dana Desa', 'Bagi Hasil Pajak & Retribusi', 'Bantuan Keuangan', 'Lain-lain Pendapatan yang Sah'],
        'belanja' => ['Penyelenggaraan Pemerintahan Desa', 'Pelaksanaan Pembangunan Desa', 'Pembinaan Kemasyarakatan', 'Pemberdayaan Masyarakat', 'Penanggulangan Bencana, Darurat & Mendesak'],
        'pembiayaan' => ['Pembiayaan netto (penerimaan dikurangi pengeluaran pembiayaan)'],
    ];

    public function index(Request $request): View
    {
        $periode = ApbdesPeriode::query()->withCount('dokumen')->orderByDesc('tahun')->get();
        $baru = $request->boolean('baru');
        $dipilih = $baru ? null : ($request->filled('periode')
            ? ApbdesPeriode::query()->with(['rincian', 'dokumen'])->find($request->query('periode'))
            : null);
        $dipilih ??= $baru ? null : ApbdesPeriode::query()->with(['rincian', 'dokumen'])->orderByDesc('tahun')->first();

        return view('pages.admin.apbdes', [
            'periode' => $periode,
            'item' => $dipilih,
            'baru' => $baru,
            'rincian' => $this->rincianAwal($dipilih),
            'dokumen' => $dipilih?->dokumen ?? collect(),
        ]);
    }

    public function simpan(SimpanApbdesRequest $request, ?int $id = null): RedirectResponse
    {
        $periode = $id === null ? new ApbdesPeriode : ApbdesPeriode::query()->findOrFail($id);
        $status = StatusTerbit::from($request->input('aksi') ?: $request->input('status'));
        $baru = ! $periode->exists;
        $statusLama = $periode->exists ? $periode->status->value : null;

        $berkasBaru = null;
        try {
            DB::transaction(function () use ($request, $periode, $status, &$berkasBaru) {
                $periode->tahun = $request->integer('tahun');
                $periode->periode = $request->string('periode')->toString();
                $periode->status = $status;
                $periode->diterbitkan_at = $status === StatusTerbit::Terbit ? ($periode->diterbitkan_at ?? now()) : null;
                // Data yang diinput admin adalah data nyata, bukan contoh.
                $periode->contoh = false;
                $periode->save();

                // Total dihitung dari rincian, bukan diketik: rincian diganti seluruhnya sesuai isian.
                $periode->rincian()->delete();
                foreach (JenisAnggaran::cases() as $jenis) {
                    foreach ((array) $request->input("rincian.{$jenis->value}", []) as $urutan => $baris) {
                        $periode->rincian()->create([
                            'jenis' => $jenis,
                            'uraian' => $baris['uraian'],
                            'anggaran' => $baris['anggaran'] === '' ? null : (int) str_replace('.', '', $baris['anggaran']),
                            'urutan' => $urutan + 1,
                        ]);
                    }
                }

                if ($request->hasFile('dokumen')) {
                    $berkas = $request->file('dokumen');
                    $berkasBaru = UnggahBerkas::simpanPdf($berkas, 'apbdes');
                    $periode->dokumen()->create([
                        'judul' => $request->string('judul_dokumen')->toString(),
                        'keterangan_periode' => $request->filled('keterangan_periode') ? $request->string('keterangan_periode')->toString() : null,
                        'path' => $berkasBaru,
                        'ukuran' => $berkas->getSize(),
                        'diunggah_oleh' => $request->user()->id,
                    ]);
                }
            });
        } catch (\Throwable $e) {
            UnggahBerkas::hapusPdf($berkasBaru);

            throw $e;
        }

        Aktivitas::simpanKonten('apbdes', $periode, 'APBDes '.$periode->tahun, $baru, $statusLama, $status->value);
        if ($berkasBaru !== null) {
            Aktivitas::catat('apbdes.dokumen_diunggah', $periode->dokumen()->latest('id')->first(), $request->string('judul_dokumen')->toString());
        }

        return redirect()->route('admin.apbdes', ['periode' => $periode->id])->with('toast', 'Perubahan berhasil disimpan.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $periode = ApbdesPeriode::query()->with('dokumen')->findOrFail($id);
        $berkas = $periode->dokumen->pluck('path')->all();

        $periode->delete();
        Aktivitas::catat('apbdes.dihapus', $periode, 'APBDes '.$periode->tahun);
        foreach ($berkas as $path) {
            UnggahBerkas::hapusPdf($path);
        }

        return redirect()->route('admin.apbdes')->with('toast', 'Periode APBDes berhasil dihapus.');
    }

    public function dokumenHapus(ApbdesDokumen $dokumen): RedirectResponse
    {
        $periodeId = $dokumen->periode_id;
        $path = $dokumen->path;

        $dokumen->delete();
        UnggahBerkas::hapusPdf($path);
        Aktivitas::catat('apbdes.dokumen_dihapus', $dokumen, $dokumen->judul);

        return redirect()->route('admin.apbdes', ['periode' => $periodeId])->with('toast', 'Dokumen berhasil dihapus.');
    }

    /**
     * Unduhan untuk admin (termasuk dokumen periode yang masih draf).
     */
    public function dokumenUnduh(ApbdesDokumen $dokumen): StreamedResponse
    {
        abort_unless(filled($dokumen->path) && Storage::disk('local')->exists($dokumen->path), 404);

        return UnggahBerkas::unduhPdf($dokumen->path, $dokumen->judul);
    }

    /**
     * Isian rincian untuk formulir: dari old input, dari database, atau uraian bawaan untuk periode baru.
     *
     * @return array<string, list<array{uraian: string, anggaran: string}>>
     */
    private function rincianAwal(?ApbdesPeriode $periode): array
    {
        $hasil = [];

        foreach (JenisAnggaran::cases() as $jenis) {
            $lama = old("rincian.{$jenis->value}");

            if (is_array($lama)) {
                $hasil[$jenis->value] = collect($lama)->map(fn ($b) => ['uraian' => (string) ($b['uraian'] ?? ''), 'anggaran' => (string) ($b['anggaran'] ?? '')])->values()->all();

                continue;
            }

            $baris = $periode?->rincian->where('jenis', $jenis)->values();

            $hasil[$jenis->value] = $baris && $baris->isNotEmpty()
                ? $baris->map(fn ($r) => ['uraian' => $r->uraian, 'anggaran' => $r->anggaran === null ? '' : number_format($r->anggaran, 0, ',', '.')])->all()
                : array_map(fn (string $u) => ['uraian' => $u, 'anggaran' => ''], $periode ? [] : self::URAIAN_BAWAAN[$jenis->value]);
        }

        return $hasil;
    }
}
