<?php

namespace App\Http\Controllers;

use App\Support\DemoStore;
use App\Support\Format;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminAspirasiController extends Controller
{
    // TODO(backend): middleware `auth`, pengecekan izin, dan penyimpanan ke database.
    // Aspirasi tidak pernah tampil di halaman publik; NIK hanya tampil disamarkan.

    private const STATUS = ['baru' => 'Baru', 'dibaca' => 'Sudah Dibaca', 'selesai' => 'Selesai'];

    public function index(Request $request): View
    {
        $semua = collect(DemoStore::daftar('aspirasi'))->sortByDesc('diterima')->values();
        $filter = array_key_exists($request->query('status'), self::STATUS) ? $request->query('status') : 'semua';
        $kata = trim((string) $request->query('q', ''));

        $daftar = $semua
            ->when($filter !== 'semua', fn ($c) => $c->where('status', $filter))
            ->when($kata !== '', fn ($c) => $c->filter(fn (array $a) => str_contains(mb_strtolower($a['nama'].' '.$a['pesan']), mb_strtolower($kata))))
            ->values();

        $dipilih = $request->query('id') ? $daftar->firstWhere('id', $request->query('id')) : null;

        return view('pages.admin.aspirasi', [
            'daftar' => $daftar,
            'filter' => $filter,
            'jumlahBaru' => $semua->where('status', 'baru')->count(),
            // Di layar lebar, detail pertama otomatis terbuka; di HP daftar tampil lebih dulu.
            'dipilih' => $dipilih,
            'bawaan' => $daftar->first(),
            'statusLabel' => self::STATUS,
            'semuaKosong' => $semua->isEmpty(),
        ]);
    }

    public function status(Request $request, string $id): RedirectResponse
    {
        $item = DemoStore::item('aspirasi', $id);
        abort_if($item === null, 404);

        $valid = $request->validate([
            'status' => ['required', 'in:baru,dibaca,selesai'],
            'aksi' => ['nullable', 'in:dibaca,selesai'],
            'catatan' => ['nullable', 'string', 'max:2000'],
        ], ['in' => 'Status tidak dikenal.', 'max' => 'Catatan terlalu panjang (maksimal :max karakter).']);

        // Tombol yang ditekan menentukan status akhir; tanpa tombol, pilihan "Ubah status" dipakai.
        $status = $valid['aksi'] ?? $valid['status'];
        $riwayat = $item['riwayat'];

        if ($status !== $item['status']) {
            $riwayat[] = ['waktu' => now()->format('Y-m-d H:i'), 'teks' => 'Status diubah menjadi '.self::STATUS[$status]];
        }
        if (($valid['catatan'] ?? null) && ($valid['catatan'] !== ($item['catatan'] ?? null))) {
            $riwayat[] = ['waktu' => now()->format('Y-m-d H:i'), 'teks' => 'Catatan tindak lanjut diperbarui'];
        }

        DemoStore::simpan('aspirasi', ['status' => $status, 'catatan' => $valid['catatan'] ?? null, 'riwayat' => $riwayat], $id);

        return redirect()->route('admin.aspirasi.index', ['id' => $id])->with('toast', 'Perubahan berhasil disimpan.');
    }
}
