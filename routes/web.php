<?php

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
