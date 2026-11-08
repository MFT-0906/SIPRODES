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

    // Proksi tepercaya (daftar IP dipisah koma, atau * bila proxy tidak dapat diketahui). Kosong = tidak ada.
    // Dibaca lewat config agar tetap bekerja saat `php artisan config:cache` dipakai di produksi.
    'proksi_tepercaya' => env('TRUSTED_PROXIES'),

    // Asal warga transmigrasi bedol desa (sumber: proposal desa), untuk panel perjalanan di halaman Profil.
    'asal' => [
        'desa' => 'Kaligesik',
        'wilayah' => 'Kec. Srumbung, Kab. Magelang, Jawa Tengah',
    ],

    // Wilayah administrasi untuk header, footer, dan halaman kontak.
    'wilayah' => [
        'sebutan' => 'Desa',
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
        // NIK tidak lagi diminta di formulir. Kolom NIK di database dipertahankan hanya untuk data lama
        // (tetap terenkripsi, tampil disamarkan, dan ikut dianonimkan).
        // Masa simpan nama (dan NIK data lama) setelah aspirasi selesai, lalu dianonimkan.
        'masa_simpan_bulan' => (int) env('ASPIRASI_MASA_SIMPAN_BULAN', 24),
        // Waktu isi minimum formulir (detik) untuk anti-spam.
        'waktu_isi_minimum_detik' => (int) env('ASPIRASI_WAKTU_MINIMUM', 3),
        // true: saat anonimisasi, isi pesan juga diganti teks netral (bukan hanya nama dan NIK).
        'hapus_pesan_saat_anonim' => (bool) env('ASPIRASI_HAPUS_PESAN', false),
    ],

    // Audit log (Fase 10): entri lebih tua dari ini dihapus oleh perintah log:bersihkan (0 = tidak pernah dihapus).
    'log' => [
        'masa_simpan_bulan' => (int) env('LOG_AKTIVITAS_MASA_SIMPAN_BULAN', 24),
    ],

    // Atur ulang kata sandi & pembatasan login (Fase 2).
    'auth' => [
        'kata_sandi_min' => 10,
        'percobaan_maks' => (int) env('LOGIN_PERCOBAAN_MAKS', 5),
        'kunci_menit' => (int) env('LOGIN_KUNCI_MENIT', 15),
    ],

];
