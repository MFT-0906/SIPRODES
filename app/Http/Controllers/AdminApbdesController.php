<?php

namespace App\Http\Controllers;

use App\Support\DemoStore;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminApbdesController extends Controller
{
    // TODO(backend): pemeriksaan isi PDF di server dan penyimpanan ke database.

    public function index(Request $request): View
    {
        $periode = collect(DemoStore::daftar('apbdes'))->sortByDesc('tahun')->values();
        $baru = $request->boolean('baru');
        $dipilih = $baru ? null : ($periode->firstWhere('id', $request->query('periode')) ?? $periode->first());

        return view('pages.admin.apbdes', ['periode' => $periode, 'item' => $dipilih, 'baru' => $baru]);
    }

    public function simpan(Request $request, ?string $id = null): RedirectResponse
    {
        abort_if($id !== null && DemoStore::item('apbdes', $id) === null, 404);

        $angka = ['required', 'regex:/^(\d{1,3}(\.\d{3})+|\d+)(,\d+)?$/'];
        $valid = $request->validate([
            'tahun' => ['required', 'integer', 'between:2000,2100'],
            'periode' => ['required', 'string', 'max:50'],
            'pendapatan' => $angka,
            'belanja' => $angka,
            // TODO(backend): jenis dan isi berkas wajib diperiksa di server, bukan hanya berdasarkan ekstensi.
            'dokumen' => ['nullable', 'file', 'mimes:pdf'],
            'status' => ['required', 'in:draf,terbit'],
            'aksi' => ['nullable', 'in:draf,terbit'],
        ], [
            'required' => ':attribute wajib diisi.',
            'integer' => ':attribute harus berupa angka tahun.',
            'between' => ':attribute di luar rentang yang benar.',
            'regex' => 'Masukkan angka saja tanpa huruf.',
            'mimes' => 'Ditolak: hanya PDF yang diizinkan. Pilih berkas lain.',
            'max' => ':attribute terlalu panjang.',
        ], ['tahun' => 'Tahun', 'periode' => 'Periode', 'pendapatan' => 'Total pendapatan', 'belanja' => 'Total belanja']);

        $rupiah = fn (string $nilai) => (float) str_replace(',', '.', str_replace('.', '', $nilai));
        $id ??= (string) $valid['tahun'];

        DemoStore::simpan('apbdes', [
            'tahun' => (int) $valid['tahun'], 'periode' => $valid['periode'],
            'pendapatan' => $rupiah($valid['pendapatan']), 'belanja' => $rupiah($valid['belanja']),
            'dokumen' => DemoStore::item('apbdes', $id)['dokumen'] ?? [],
            'status' => $valid['aksi'] ?? $valid['status'],
        ], $id);

        return redirect()->route('admin.apbdes', ['periode' => $id])->with('toast', 'Perubahan berhasil disimpan.');
    }

    public function destroy(string $id): RedirectResponse
    {
        abort_if(DemoStore::item('apbdes', $id) === null, 404);

        DemoStore::hapus('apbdes', $id);

        return redirect()->route('admin.apbdes')->with('toast', 'Periode APBDes berhasil dihapus.');
    }
}
