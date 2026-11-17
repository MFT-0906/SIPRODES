<?php

namespace App\Http\Controllers;

use App\Support\StatistikPengunjung;
use Illuminate\Contracts\View\View;

/**
 * Halaman "Statistik Pengunjung" di admin (modul `statistik`; Super Admin selalu boleh).
 * Semua angka berasal dari agregat harian; tidak ada data pribadi pengunjung di basis data.
 */
class AdminStatistikController extends Controller
{
    public function index(): View
    {
        return view('pages.admin.statistik', [
            'aktif' => (bool) config('desa.statistik.aktif'),
            'masaSimpanBulan' => (int) config('desa.statistik.masa_simpan_bulan'),
            'ringkasan' => StatistikPengunjung::ringkasan(),
            'harian' => StatistikPengunjung::harian30(),
            'terpopuler' => StatistikPengunjung::terpopuler30(),
        ]);
    }
}
