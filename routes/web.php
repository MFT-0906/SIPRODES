<?php

use App\Http\Controllers\AdminAgendaController;
use App\Http\Controllers\AdminAkunController;
use App\Http\Controllers\AdminApbdesController;
use App\Http\Controllers\AdminAspirasiController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminBeritaController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminPotensiController;
use App\Http\Controllers\AdminProfilController;
use App\Http\Controllers\AdminStrukturController;
use App\Http\Controllers\AspirasiController;
use App\Http\Controllers\PublicPageController;
use App\Http\Middleware\PastikanAkunAktif;
use Illuminate\Support\Facades\Route;

// Halaman publik (docs/planFE.md fase 3–7).
Route::get('/', [PublicPageController::class, 'home'])->name('home');
Route::get('/profil-desa', [PublicPageController::class, 'profil'])->name('profil');
Route::get('/transparansi-apbdes', [PublicPageController::class, 'apbdes'])->name('apbdes');
Route::get('/transparansi-apbdes/dokumen/{dokumen}', [PublicPageController::class, 'unduhDokumen'])->whereNumber('dokumen')->name('apbdes.unduh');
Route::get('/potensi-desa', [PublicPageController::class, 'potensi'])->name('potensi');
Route::get('/berita', [PublicPageController::class, 'berita'])->name('berita.index');
Route::get('/berita/{slug}', [PublicPageController::class, 'beritaShow'])->name('berita.show');
Route::get('/kontak-lokasi', [PublicPageController::class, 'kontak'])->name('kontak');

Route::get('/ruang-aspirasi', [AspirasiController::class, 'create'])->name('aspirasi.create');
Route::post('/ruang-aspirasi', [AspirasiController::class, 'store'])->middleware('throttle:aspirasi')->name('aspirasi.store');

// Halaman uji internal, hanya untuk lingkungan lokal.
if (app()->environment('local')) {
    Route::view('/_tokens', 'dev.tokens');
    Route::view('/_komponen', 'dev.komponen');
}

// Area admin (docs/planFE.md fase 8–10; autentikasi docs/planBE.md fase 2).
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/masuk', [AdminAuthController::class, 'login'])->name('login');
        Route::post('/masuk', [AdminAuthController::class, 'masuk'])->middleware('throttle:30,1')->name('login.store');
        Route::get('/lupa-kata-sandi', [AdminAuthController::class, 'lupa'])->name('password.request');
        Route::post('/lupa-kata-sandi', [AdminAuthController::class, 'kirimTautan'])->middleware('throttle:5,1')->name('password.email');
        Route::get('/atur-ulang-kata-sandi/{token}', [AdminAuthController::class, 'formAturUlang'])->name('password.reset');
        Route::post('/atur-ulang-kata-sandi', [AdminAuthController::class, 'aturUlang'])->middleware('throttle:10,1')->name('password.update');
    });

    // Izin per modul ditegakkan di server: middleware `modul:*` memakai Gate `akses-modul`.
    Route::middleware(['auth', PastikanAkunAktif::class])->group(function () {
        Route::post('/keluar', [AdminAuthController::class, 'keluar'])->name('logout');

        Route::middleware('modul:profil')->group(function () {

            Route::get('/profil', [AdminProfilController::class, 'show'])->name('profil');
            Route::put('/profil/{tab}', [AdminProfilController::class, 'simpan'])->name('profil.simpan');

            Route::get('/struktur-perangkat', [AdminStrukturController::class, 'index'])->name('struktur');
            Route::post('/struktur-perangkat', [AdminStrukturController::class, 'store'])->name('struktur.store');
            Route::put('/struktur-perangkat/{id}', [AdminStrukturController::class, 'update'])->whereNumber('id')->name('struktur.update');
            Route::delete('/struktur-perangkat/{id}', [AdminStrukturController::class, 'destroy'])->whereNumber('id')->name('struktur.destroy');
            Route::post('/struktur-perangkat/{id}/geser', [AdminStrukturController::class, 'geser'])->whereNumber('id')->name('struktur.geser');
        });

        Route::middleware('modul:apbdes')->group(function () {

            Route::get('/apbdes', [AdminApbdesController::class, 'index'])->name('apbdes');
            Route::post('/apbdes', [AdminApbdesController::class, 'simpan'])->name('apbdes.store');
            Route::put('/apbdes/{id}', [AdminApbdesController::class, 'simpan'])->whereNumber('id')->name('apbdes.update');
            Route::delete('/apbdes/{id}', [AdminApbdesController::class, 'destroy'])->whereNumber('id')->name('apbdes.destroy');
            Route::delete('/apbdes/dokumen/{dokumen}', [AdminApbdesController::class, 'dokumenHapus'])->whereNumber('dokumen')->name('apbdes.dokumen.destroy');
            Route::get('/apbdes/dokumen/{dokumen}/unduh', [AdminApbdesController::class, 'dokumenUnduh'])->whereNumber('dokumen')->name('apbdes.dokumen.unduh');
        });

        Route::middleware('modul:potensi')->group(function () {

            Route::get('/potensi-pertanian', [AdminPotensiController::class, 'pertanian'])->name('pertanian');
            Route::post('/potensi-pertanian', [AdminPotensiController::class, 'komoditasSimpan'])->name('pertanian.store');
            Route::put('/potensi-pertanian/{id}', [AdminPotensiController::class, 'komoditasSimpan'])->whereNumber('id')->name('pertanian.update');
            Route::delete('/potensi-pertanian/{id}', [AdminPotensiController::class, 'komoditasHapus'])->whereNumber('id')->name('pertanian.destroy');

            Route::get('/kelompok-tani', [AdminPotensiController::class, 'kelompok'])->name('kelompok-tani');
            Route::post('/kelompok-tani', [AdminPotensiController::class, 'kelompokSimpan'])->name('kelompok-tani.store');
            Route::put('/kelompok-tani/{id}', [AdminPotensiController::class, 'kelompokSimpan'])->whereNumber('id')->name('kelompok-tani.update');
            Route::delete('/kelompok-tani/{id}', [AdminPotensiController::class, 'kelompokHapus'])->whereNumber('id')->name('kelompok-tani.destroy');

            Route::get('/umkm', [AdminPotensiController::class, 'umkm'])->name('umkm');
            Route::post('/umkm', [AdminPotensiController::class, 'umkmSimpan'])->name('umkm.store');
            Route::put('/umkm/{id}', [AdminPotensiController::class, 'umkmSimpan'])->whereNumber('id')->name('umkm.update');
            Route::delete('/umkm/{id}', [AdminPotensiController::class, 'umkmHapus'])->whereNumber('id')->name('umkm.destroy');
        });

        Route::middleware('modul:aspirasi')->group(function () {

            Route::get('/aspirasi', [AdminAspirasiController::class, 'index'])->name('aspirasi.index');
            Route::put('/aspirasi/{id}', [AdminAspirasiController::class, 'status'])->whereNumber('id')->name('aspirasi.status');
        });

        Route::middleware('modul:akun')->group(function () {

            Route::get('/akun', [AdminAkunController::class, 'index'])->name('akun.index');
            Route::post('/akun', [AdminAkunController::class, 'store'])->name('akun.store');
            Route::put('/akun/{user}', [AdminAkunController::class, 'update'])->name('akun.update');
            Route::post('/akun/{user}/atur-ulang', [AdminAkunController::class, 'reset'])->name('akun.reset');
        });

        Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

        Route::middleware('modul:berita')->group(function () {
            Route::get('/berita', [AdminBeritaController::class, 'index'])->name('berita.index');
            Route::get('/berita/tambah', [AdminBeritaController::class, 'create'])->name('berita.create');
            Route::post('/berita', [AdminBeritaController::class, 'store'])->name('berita.store');
            Route::get('/berita/{slug}/ubah', [AdminBeritaController::class, 'edit'])->name('berita.edit');
            Route::put('/berita/{slug}', [AdminBeritaController::class, 'update'])->name('berita.update');
            Route::delete('/berita/{slug}', [AdminBeritaController::class, 'destroy'])->name('berita.destroy');

            Route::get('/agenda', [AdminAgendaController::class, 'index'])->name('agenda.index');
            Route::post('/agenda', [AdminAgendaController::class, 'store'])->name('agenda.store');
            Route::put('/agenda/{id}', [AdminAgendaController::class, 'update'])->whereNumber('id')->name('agenda.update');
            Route::delete('/agenda/{id}', [AdminAgendaController::class, 'destroy'])->whereNumber('id')->name('agenda.destroy');
        });
    });
});
