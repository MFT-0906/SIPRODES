<?php

namespace App\Support;

/**
 * Data sementara untuk pengembangan antarmuka.
 *
 * Semua isi di sini hanya contoh dan tidak boleh dianggap data resmi desa.
 * Template Blade tidak boleh menulis data langsung; ambil dari kelas ini.
 */
class DemoContent
{
    // TODO(backend): ganti dengan data dari database

    /**
     * Identitas dan kontak desa untuk header, footer, dan halaman kontak.
     * Nama dan wilayah mengikuti desain Figma; kontak resmi belum diverifikasi sehingga tidak ditampilkan.
     *
     * @return array<string, mixed>
     */
    public static function identitas(): array
    {
        return [
            'sebutan' => 'Pemerintah Desa',
            'nama' => 'Marga Agung',
            'wilayah' => 'Kecamatan Jati Agung, Kabupaten Lampung Selatan, Provinsi Lampung.',
            'wilayah_strip' => 'Kecamatan Jati Agung · Kabupaten Lampung Selatan · Provinsi Lampung',
            'wilayah_strip_pendek' => 'Kec. Jati Agung · Kab. Lampung Selatan · Lampung',
            // Isi `value` dan ubah `verified` menjadi true hanya setelah pemerintah desa memverifikasi.
            'kontak' => [
                'alamat' => ['verified' => false, 'value' => null],
                'telepon' => ['verified' => false, 'value' => null],
                'email' => ['verified' => false, 'value' => null],
            ],
        ];
    }

    /**
     * Contoh isi untuk katalog komponen (/_komponen). Teks mengikuti contoh di Figma.
     *
     * @return array<string, mixed>
     */
    public static function katalog(): array
    {
        return [
            'breadcrumb' => [
                ['label' => 'Beranda', 'href' => '#'],
                ['label' => 'Berita', 'href' => '#'],
                ['label' => 'Halaman saat ini'],
            ],
            'kategori' => [
                'kegiatan' => 'Kegiatan',
                'pemerintahan' => 'Pemerintahan',
                'pengumuman' => 'Pengumuman',
            ],
            'status' => [
                'semua' => 'Semua',
                'terbit' => 'Terbit',
                'draf' => 'Draf',
            ],
            'berita' => [
                'title' => 'Judul berita contoh — ganti dengan berita resmi',
                'excerpt' => 'Ringkasan singkat isi berita dua sampai tiga baris agar mudah dipindai oleh warga.',
                'date' => '12 Okt 2026',
                'category' => 'Kegiatan',
                'href' => '#',
            ],
            'umkm' => [
                'name' => 'Nama Usaha',
                'products' => 'keripik, olahan jagung',
                'description' => 'Deskripsi singkat usaha dan produk unggulan.',
                'whatsapp' => '0000 0000 0000',
            ],
            'agenda' => [
                'day' => '05',
                'month' => 'NOV',
                'title' => 'Nama agenda contoh',
                'time' => '08.00 WIB',
                'location' => 'Lokasi (jika terverifikasi)',
            ],
            'akses' => [
                'title' => 'APBDes',
                'description' => 'Ringkasan & dokumen PDF',
                'icon' => 'wallet',
                'href' => '#',
            ],
            'statistik' => [
                'label' => 'Jumlah penduduk',
                'value' => '± 4.492 jiwa',
                'source' => 'Sumber: survei awal 2026 · perlu verifikasi desa',
            ],
            'perangkat' => [
                'name' => 'Nama (menunggu data)',
                'position' => 'Jabatan',
            ],
        ];
    }
}
