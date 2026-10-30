<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AspirasiController;
use App\Http\Controllers\PublicPageController;
use Illuminate\Support\Facades\Route;

// Halaman publik (docs/planFE.md fase 3–7).
Route::get('/', [PublicPageController::class, 'home'])->name('home');
Route::get('/profil-desa', [PublicPageController::class, 'profil'])->name('profil');
Route::get('/transparansi-apbdes', [PublicPageController::class, 'apbdes'])->name('apbdes');
Route::get('/potensi-desa', [PublicPageController::class, 'potensi'])->name('potensi');
Route::get('/berita', [PublicPageController::class, 'berita'])->name('berita.index');
Route::get('/berita/{slug}', [PublicPageController::class, 'beritaShow'])->name('berita.show');
Route::get('/kontak-lokasi', [PublicPageController::class, 'kontak'])->name('kontak');

Route::get('/ruang-aspirasi', [AspirasiController::class, 'create'])->name('aspirasi.create');
// TODO(backend): pembatasan spam final disepakati bersama desa; ini hanya pembatas dasar.
Route::post('/ruang-aspirasi', [AspirasiController::class, 'store'])->middleware('throttle:5,1')->name('aspirasi.store');

// Halaman uji internal, hanya untuk lingkungan lokal.
if (app()->environment('local')) {
    Route::view('/_tokens', 'dev.tokens');
    Route::view('/_komponen', 'dev.komponen');
}

// Area admin (docs/planFE.md fase 8–10).
// TODO(backend): `guest` untuk masuk/lupa kata sandi, `auth` + izin per peran untuk halaman lain.
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/masuk', [AdminAuthController::class, 'login'])->name('login');
    Route::post('/masuk', [AdminAuthController::class, 'masuk'])->middleware('throttle:5,1')->name('login.store');
    Route::get('/lupa-kata-sandi', [AdminAuthController::class, 'lupa'])->name('password.request');
    Route::post('/lupa-kata-sandi', [AdminAuthController::class, 'kirimTautan'])->middleware('throttle:5,1')->name('password.email');
    Route::post('/keluar', [AdminAuthController::class, 'keluar'])->name('logout');

    Route::get('/', [AdminController::class, 'halaman'])->name('dashboard');
    Route::get('/profil', [AdminController::class, 'halaman'])->name('profil');
    Route::get('/struktur-perangkat', [AdminController::class, 'halaman'])->name('struktur');
    Route::get('/apbdes', [AdminController::class, 'halaman'])->name('apbdes');
    Route::get('/potensi-pertanian', [AdminController::class, 'halaman'])->name('pertanian');
    Route::get('/kelompok-tani', [AdminController::class, 'halaman'])->name('kelompok-tani');
    Route::get('/umkm', [AdminController::class, 'halaman'])->name('umkm');
    Route::get('/berita', [AdminController::class, 'halaman'])->name('berita.index');
    Route::get('/agenda', [AdminController::class, 'halaman'])->name('agenda.index');
    Route::get('/aspirasi', [AdminController::class, 'halaman'])->name('aspirasi.index');
    Route::get('/akun', [AdminController::class, 'halaman'])->name('akun.index');
});
