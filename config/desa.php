<?php

/*
|--------------------------------------------------------------------------
| Konfigurasi Desa Marga Agung
|--------------------------------------------------------------------------
|
| Nilai bawaan sementara untuk keputusan desa yang belum dikonfirmasi
| (lihat docs/planBE.md, tabel "Keputusan & nilai bawaan"). Semua dapat
| diubah lewat .env tanpa mengubah kode.
|
*/

return [

    'nama' => env('DESA_NAMA', 'Desa Marga Agung'),

    // Wilayah administrasi untuk header, footer, dan halaman kontak.
    'wilayah' => [
        'sebutan' => 'Pemerintah Desa',
        'nama' => 'Marga Agung',
        'lengkap' => 'Kecamatan Jati Agung, Kabupaten Lampung Selatan, Provinsi Lampung.',
        'strip' => 'Kecamatan Jati Agung · Kabupaten Lampung Selatan · Provinsi Lampung',
        'strip_pendek' => 'Kec. Jati Agung · Kab. Lampung Selatan · Lampung',
    ],

    // Super Admin pertama (AdminAwalSeeder). Kata sandi acak dicetak sekali ke konsol.
    'admin' => [
        'email' => env('ADMIN_EMAIL'),
        'nama' => env('ADMIN_NAMA', 'Super Admin'),
    ],

    // Batas unggahan (Fase 8). Ukuran dalam kilobita, sesuai aturan validasi Laravel.
    'unggahan' => [
        'gambar_maks_kb' => (int) env('UNGGAH_GAMBAR_MAKS_KB', 2048),
        'gambar_maks_piksel' => (int) env('UNGGAH_GAMBAR_MAKS_PIKSEL', 4000),
        // Gambar yang lebih panjang dari ini dikecilkan saat disimpan (hemat bandwidth, NFR-01).
        'gambar_sisi_panjang' => (int) env('UNGGAH_GAMBAR_SISI_PANJANG', 1920),
        'gambar_mimetypes' => ['image/jpeg', 'image/png', 'image/webp'],
        'pdf_maks_kb' => (int) env('UNGGAH_PDF_MAKS_KB', 10240),
    ],

    // Ruang Aspirasi (Fase 9).
    'aspirasi' => [
        // NIK opsional, disimpan terenkripsi, tampil disamarkan.
        'nik_aktif' => (bool) env('ASPIRASI_NIK', true),
        // Masa simpan nama & NIK setelah aspirasi selesai, lalu dianonimkan.
        'masa_simpan_bulan' => (int) env('ASPIRASI_MASA_SIMPAN_BULAN', 24),
        // Waktu isi minimum formulir (detik) untuk anti-spam.
        'waktu_isi_minimum_detik' => (int) env('ASPIRASI_WAKTU_MINIMUM', 3),
    ],

    // Atur ulang kata sandi & pembatasan login (Fase 2).
    'auth' => [
        'kata_sandi_min' => 10,
        'percobaan_maks' => (int) env('LOGIN_PERCOBAAN_MAKS', 5),
        'kunci_menit' => (int) env('LOGIN_KUNCI_MENIT', 15),
    ],

];
