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
     * Ringkasan APBDes per tahun. Semua angka masih kosong (null) karena nilai resmi belum diinput;
     * nama dokumen hanyalah contoh dari desain dan belum memiliki berkas.
     *
     * @return array<string, mixed>
     */
    public static function apbdes(): array
    {
        $pendapatan = ['Pendapatan Asli Desa', 'Dana Desa', 'Alokasi Dana Desa', 'Bagi Hasil Pajak & Retribusi', 'Bantuan Keuangan', 'Lain-lain Pendapatan yang Sah'];
        $belanja = ['Penyelenggaraan Pemerintahan Desa', 'Pelaksanaan Pembangunan Desa', 'Pembinaan Kemasyarakatan', 'Pemberdayaan Masyarakat', 'Penanggulangan Bencana, Darurat & Mendesak'];

        $tahun = fn (int $tahun, array $dokumen = []) => [
            'periode' => "1 Januari – 31 Desember {$tahun}",
            // false = angka dan dokumen masih contoh/kosong dan harus diberi penanda di halaman.
            'resmi' => false,
            'ringkasan' => ['pendapatan' => null, 'belanja' => null, 'pembiayaan' => null],
            'pendapatan' => ['rows' => array_map(fn ($uraian) => ['uraian' => $uraian, 'anggaran' => null], $pendapatan), 'total' => null],
            'belanja' => ['rows' => array_map(fn ($uraian) => ['uraian' => $uraian, 'anggaran' => null], $belanja), 'total' => null],
            'dokumen' => $dokumen,
        ];

        return [
            'pengantar' => 'Ringkasan Anggaran Pendapatan dan Belanja Desa (APBDes) Marga Agung beserta dokumen resminya. Seluruh angka bersumber dari dokumen APBDes yang telah ditetapkan.',
            'tahun_default' => 2026,
            'tahun' => [
                2026 => $tahun(2026, [
                    ['judul' => 'Peraturan Desa tentang APBDes Tahun 2026', 'periode' => 'Jan–Des 2026', 'url' => null, 'ukuran' => null, 'diunggah' => null],
                    ['judul' => 'Laporan Realisasi APBDes Semester I 2026', 'periode' => 'Jan–Jun 2026', 'url' => null, 'ukuran' => null, 'diunggah' => null],
                ]),
                2025 => $tahun(2025),
                2024 => $tahun(2024),
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
