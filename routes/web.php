<?php

use App\Http\Controllers\PublicPageController;
use Illuminate\Support\Facades\Route;

// Halaman publik. Isinya masih kerangka berjudul; dikerjakan pada fase 4–6 (docs/planFE.md).
Route::get('/', [PublicPageController::class, 'home'])->name('home');
Route::get('/profil-desa', [PublicPageController::class, 'profil'])->name('profil');
Route::view('/transparansi-apbdes', 'pages.public.stub', ['title' => 'Transparansi APBDes'])->name('apbdes');
Route::view('/potensi-desa', 'pages.public.stub', ['title' => 'Potensi Desa'])->name('potensi');
Route::view('/berita', 'pages.public.stub', ['title' => 'Berita & Agenda'])->name('berita.index');
Route::view('/berita/{slug}', 'pages.public.stub', ['title' => 'Detail Berita'])->name('berita.show');
Route::view('/ruang-aspirasi', 'pages.public.stub', ['title' => 'Ruang Aspirasi'])->name('aspirasi.create');
Route::view('/kontak-lokasi', 'pages.public.stub', ['title' => 'Kontak & Lokasi'])->name('kontak');

// Halaman uji internal, hanya untuk lingkungan lokal.
if (app()->environment('local')) {
    Route::view('/_tokens', 'dev.tokens');
    Route::view('/_komponen', 'dev.komponen');
}
