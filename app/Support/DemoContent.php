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
            // Peta/tautan lokasi hanya tampil setelah koordinat atau alamat diverifikasi.
            'peta' => ['verified' => false, 'url' => null],
        ];
    }

    /**
     * Angka ringkas desa. Berasal dari survei awal 2026 (proposal) dan perlu diverifikasi desa.
     *
     * @return list<array{label: string, value: string, source: string}>
     */
    public static function statistik(): array
    {
        $sumber = 'Sumber: survei awal 2026 · perlu verifikasi desa';

        return [
            ['label' => 'Jumlah penduduk', 'value' => '± 4.492 jiwa', 'source' => $sumber],
            ['label' => 'Kepala keluarga', 'value' => '1.309 KK', 'source' => $sumber],
            ['label' => 'Luas wilayah', 'value' => '1.050 ha', 'source' => $sumber],
            ['label' => 'Jumlah dusun', 'value' => '6 dusun', 'source' => $sumber],
        ];
    }

    /**
     * Isi Beranda. Berita, agenda, dan UMKM hanyalah contoh dari desain, bukan data resmi.
     *
     * @return array<string, mixed>
     */
    public static function beranda(): array
    {
        return [
            'hero' => [
                'eyebrow' => 'Website resmi Pemerintah Desa',
                'judul' => 'Selamat datang di Desa Marga Agung',
                'deskripsi' => 'Desa di Kecamatan Jati Agung, Lampung Selatan, yang dibuka warga transmigrasi bedol desa asal Kaligesik, Magelang. Di sini Anda dapat melihat profil desa, anggaran, potensi, dan kabar terbaru.',
                'foto' => null,
            ],
            'akses' => [
                ['title' => 'APBDes', 'description' => 'Ringkasan & dokumen PDF', 'icon' => 'wallet', 'route' => 'apbdes'],
                ['title' => 'Profil Desa', 'description' => 'Sejarah, visi-misi, perangkat', 'icon' => 'building', 'route' => 'profil'],
                ['title' => 'Potensi Desa', 'description' => 'Pertanian & UMKM', 'icon' => 'sprout', 'route' => 'potensi'],
                ['title' => 'Berita & Agenda', 'description' => 'Kabar & kegiatan terbaru', 'icon' => 'newspaper', 'route' => 'berita.index'],
                ['title' => 'Ruang Aspirasi', 'description' => 'Kirim pertanyaan & saran', 'icon' => 'message', 'route' => 'aspirasi.create'],
            ],
            'potensi' => [
                'pengantar' => 'Sebagian besar warga bekerja sebagai petani, dengan jagung sebagai komoditas utama desa.',
                'unggulan' => [
                    'badge' => 'Komoditas unggulan',
                    'nama' => 'Jagung',
                    'deskripsi' => 'Komoditas utama sekaligus produk unggulan Desa Marga Agung. Informasi musim panen dan kontak kelompok tani ditampilkan setelah dikonfirmasi.',
                    'foto_keterangan' => 'Foto ladang jagung',
                ],
                // Nomor WhatsApp baru boleh tampil setelah pemiliknya menyetujui (whatsappApproved).
                'umkm' => [
                    ['name' => 'Dapur Jagung Bersama', 'products' => 'Keripik jagung renyah', 'description' => 'Camilan olahan jagung untuk teman minum teh dan bingkisan keluarga.', 'whatsapp' => null, 'whatsappApproved' => false],
                    ['name' => 'Anyaman Sahabat Desa', 'products' => 'Keranjang & wadah anyaman', 'description' => 'Kerajinan anyaman untuk kebutuhan rumah dan kemasan hadiah.', 'whatsapp' => null, 'whatsappApproved' => false],
                ],
            ],
            'berita' => [
                ['title' => 'Gotong royong, lingkungan bersih dan warga makin akrab', 'excerpt' => 'Warga bersama-sama membersihkan jalan dan saluran air di lingkungan desa.', 'date' => '18 Okt 2026', 'category' => 'Kegiatan warga', 'slug' => 'gotong-royong-lingkungan-bersih'],
                ['title' => 'Mengolah jagung menjadi peluang usaha bersama', 'excerpt' => 'Cerita tentang hasil kebun yang dapat diolah menjadi produk bernilai tambah.', 'date' => '15 Okt 2026', 'category' => 'Pertanian', 'slug' => 'mengolah-jagung-peluang-usaha'],
            ],
            'agenda' => [
                ['day' => '05', 'month' => 'NOV', 'title' => 'Musyawarah Desa', 'time' => '09.00 WIB', 'location' => 'Lokasi kegiatan'],
                ['day' => '12', 'month' => 'NOV', 'title' => 'Posyandu', 'time' => '08.00 WIB', 'location' => 'Lokasi kegiatan'],
                ['day' => '20', 'month' => 'NOV', 'title' => 'Kerja bakti', 'time' => 'Waktu menyusul', 'location' => null],
            ],
            'aspirasi' => [
                'judul' => 'Ada pertanyaan, keluhan, atau saran?',
                'deskripsi' => 'Sampaikan melalui Ruang Aspirasi. Pesan Anda dibaca perangkat desa yang berwenang dan tidak ditampilkan ke publik.',
            ],
        ];
    }

    /**
     * Isi Profil Desa. Visi, misi, serta nama dan foto perangkat masih menunggu naskah resmi desa.
     *
     * @return array<string, mixed>
     */
    public static function profil(): array
    {
        return [
            'pengantar' => 'Mengenal sejarah, visi dan misi, struktur pemerintahan, serta letak geografis Desa Marga Agung.',
            'sejarah' => [
                'utama' => 'Desa Marga Agung merupakan wilayah transmigrasi bedol desa yang berasal dari Desa Kaligesik, Kecamatan Srumbung, Kabupaten Magelang, Jawa Tengah. Kini desa berada di Kecamatan Jati Agung, Kabupaten Lampung Selatan.',
                'lanjutan' => 'Sebagian besar warganya bekerja sebagai petani, dengan jagung sebagai komoditas utama. Kini desa terbagi dalam enam dusun dengan luas wilayah sekitar 1.050 hektare.',
            ],
            'fakta' => [
                ['label' => 'Luas wilayah', 'value' => '1.050 ha'],
                ['label' => 'Jumlah dusun', 'value' => '6 dusun'],
                ['label' => 'Jumlah penduduk', 'value' => '± 4.492 jiwa'],
                ['label' => 'Kepala keluarga', 'value' => '1.309 KK'],
                ['label' => 'Mata pencaharian utama', 'value' => 'Petani'],
            ],
            'fakta_sumber' => 'Sumber: survei awal 2026 · perlu verifikasi desa',
            // `resmi` = false berarti teks di bawah hanyalah placeholder dan harus diberi penanda di halaman.
            'visi_misi' => [
                'resmi' => false,
                'visi' => '“Visi resmi Desa Marga Agung”',
                'misi' => ['Butir misi 1', 'Butir misi 2', 'Butir misi 3', 'Butir misi 4'],
            ],
            'perangkat' => [
                'resmi' => false,
                'daftar' => collect(['Kepala Desa', 'Sekretaris Desa', 'Kaur Keuangan', 'Kaur Perencanaan', 'Kaur Tata Usaha & Umum', 'Kasi Pemerintahan', 'Kasi Kesejahteraan', 'Kasi Pelayanan'])
                    ->map(fn (string $jabatan) => ['name' => 'Nama perangkat', 'position' => $jabatan, 'photo' => null])
                    ->all(),
            ],
            'lokasi' => [
                'alamat_wilayah' => 'Desa Marga Agung, Kecamatan Jati Agung, Kabupaten Lampung Selatan, Provinsi Lampung',
            ],
        ];
    }

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
     * Katalog Potensi Desa. Komoditas Jagung berasal dari proposal; nama kelompok tani dan usaha
     * adalah contoh dari desain dan menunggu data resmi serta persetujuan publikasi.
     *
     * @return array<string, mixed>
     */
    public static function potensi(): array
    {
        return [
            'pengantar' => 'Katalog hasil pertanian, kelompok tani, dan UMKM Desa Marga Agung. Kontak hanya ditampilkan bila telah disetujui pemiliknya.',
            'komoditas' => [
                [
                    'nama' => 'Jagung',
                    'unggulan' => true,
                    'deskripsi' => 'Komoditas utama dan produk unggulan Desa Marga Agung. Jagung dapat menjadi bahan pangan dan bahan baku usaha rumahan dengan nilai tambah.',
                    'musim_panen' => null,
                    'foto_keterangan' => 'Foto jagung',
                    'peluang' => ['Pengolahan menjadi camilan jagung', 'Pengemasan produk hasil kebun', 'Kerja sama pemasaran antarusaha'],
                ],
            ],
            'kelompok_tani' => [
                'resmi' => false,
                'daftar' => [
                    ['nama' => 'Kelompok Tani 1', 'komoditas' => 'Jagung', 'dusun' => null, 'kontak' => null, 'kontak_disetujui' => false],
                    ['nama' => 'Kelompok Tani 2', 'komoditas' => 'Jagung', 'dusun' => null, 'kontak' => null, 'kontak_disetujui' => false],
                ],
            ],
            'umkm' => [
                'resmi' => false,
                'per_halaman' => 4,
                // Nomor WhatsApp baru boleh tampil setelah pemiliknya menyetujui (whatsappApproved).
                'daftar' => [
                    ['name' => 'Dapur Jagung Bersama', 'products' => 'Keripik jagung renyah', 'description' => 'Camilan olahan jagung untuk teman minum teh dan bingkisan keluarga.', 'whatsapp' => null, 'whatsappApproved' => false],
                    ['name' => 'Anyaman Sahabat Desa', 'products' => 'Keranjang & wadah anyaman', 'description' => 'Kerajinan anyaman untuk kebutuhan rumah dan kemasan hadiah.', 'whatsapp' => null, 'whatsappApproved' => false],
                    ['name' => 'Usaha warga 3', 'products' => 'kerajinan', 'description' => 'Deskripsi singkat usaha dan produknya.', 'whatsapp' => null, 'whatsappApproved' => false],
                    ['name' => 'Usaha warga 4', 'products' => 'hasil ternak', 'description' => 'Deskripsi singkat usaha dan produknya.', 'whatsapp' => null, 'whatsappApproved' => false],
                ],
            ],
            'ajakan' => [
                'judul' => 'Ingin usaha Anda tercantum di sini?',
                'deskripsi' => 'Pelaku usaha di Desa Marga Agung dapat mendaftarkan usahanya melalui kantor desa atau Ruang Aspirasi.',
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
