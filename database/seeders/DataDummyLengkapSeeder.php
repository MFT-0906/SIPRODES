<?php

namespace Database\Seeders;

use App\Enums\JenisAnggaran;
use App\Enums\StatusAspirasi;
use App\Enums\StatusTerbit;
use App\Models\Agenda;
use App\Models\ApbdesPeriode;
use App\Models\ApbdesRincian;
use App\Models\Aspirasi;
use App\Models\Berita;
use App\Models\Dusun;
use App\Models\Jabatan;
use App\Models\KelompokTani;
use App\Models\Komoditas;
use App\Models\PerangkatDesa;
use App\Models\Umkm;
use Illuminate\Database\Seeder;

/**
 * Data dummy lengkap untuk menguji tampilan halaman publik dan admin:
 * daftar, paginasi, filter, pencarian, grafik, dan berbagai status.
 * Semua baris diberi tanda `contoh = true` dan tidak dijalankan di produksi.
 */
class DataDummyLengkapSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(MasterDataSeeder::class);

        $this->berita();
        $this->agenda();
        $this->perangkat();
        $this->potensi();
        $this->apbdes();
        $this->aspirasi();
    }

    private function berita(): void
    {
        $data = [
            [
                'slug' => 'gotong-royong-dusun-empat',
                'judul' => 'Gotong Royong Membersihkan Dusun Empat',
                'ringkasan' => 'Warga Dusun Empat bersama membersihkan jalan dan saluran air setelah hujan.',
                'kategori' => 'Kegiatan',
                'tanggal' => '2026-11-02',
                'status' => StatusTerbit::Terbit,
                'isi' => implode("\n\n", [
                    'Warga Dusun Empat berkumpul pagi hari untuk membersihkan jalan dan saluran air yang tersumbat daun kering.',
                    '## Rincian Pekerjaan',
                    'Sebagian warga menyapu jalan, sementara yang lain mengangkat sampah dari saluran air. Sampah dikumpulkan di tempat yang telah disepakati.',
                    '> Lingkungan bersih adalah tanggung jawab bersama.',
                    '## Hasil Kegiatan',
                    'Setelah selesai, warga membuat kesepakatan sederhana untuk tidak membuang sampah sembarangan dan menjaga kebersihan lingkungan bersama.',
                ]),
            ],
            [
                'slug' => 'jadwal-posyandu-november',
                'judul' => 'Jadwal Posyandu Bulan November di Balai Desa',
                'ringkasan' => 'Posyandu dibuka setiap Jumat untuk pemantauan pertumbuhan balita dan imunisasi.',
                'kategori' => 'Kegiatan',
                'tanggal' => '2026-10-30',
                'status' => StatusTerbit::Terbit,
                'isi' => implode("\n\n", [
                    'Posyandu bulan November diselenggarakan di Balai Desa setiap hari Jumat pukul 08.00 hingga 11.00 WIB.',
                    '## Pelayanan yang Tersedia',
                    'Orang tua dapat membawa balita untuk pemeriksaan berat badan, tinggi badan, serta imunisasi rutin sesuai jadwal.',
                    '> Kesehatan balita ditangani oleh petugas kesehatan dan kader desa yang terlatih.',
                    '## Persiapan Warga',
                    'Diharapkan warga membawa buku kesehatan anak dan datang tepat waktu agar antrian berjalan lancar.',
                ]),
            ],
            [
                'slug' => 'posyandu-balita-dusun-dua',
                'judul' => 'Posyandu Balita di Dusun Dua Digelar',
                'ringkasan' => 'Orang tua membawa balita untuk pemeriksaan pertumbuhan dan imunisasi rutin.',
                'kategori' => 'Kegiatan',
                'tanggal' => '2026-10-10',
                'status' => StatusTerbit::Terbit,
                'isi' => implode("\n\n", [
                    'Posyandu balita di Dusun Dua digelar di tempat ibadah keliling dengan antusias warga.',
                    '## Pelayanan Kesehatan',
                    'Petugas posyandu melakukan pemeriksaan pertumbuhan, pemberian ASI eksklusif, dan imunisasi kepada balita hadir.',
                    '> Kesehatan anak adalah investasi masa depan desa.',
                    '## Edukasi Orang Tua',
                    'Selain pemeriksaan, ibu-ibu juga mendapat edukasi tentang gizi seimbang dan pencegahan stunting pada balita.',
                ]),
            ],
            [
                'slug' => 'panen-jagung-terbaik',
                'judul' => 'Pemenang Panen Jagung Terbaik Dinyatakan',
                'ringkasan' => 'Petani dengan hasil panen jagung tertinggi menerima penghargaan dari desa.',
                'kategori' => 'Pertanian',
                'tanggal' => '2026-10-28',
                'status' => StatusTerbit::Terbit,
                'isi' => implode("\n\n", [
                    'Lomba panen jagung diikuti oleh kelompok tani dari seluruh dusun untuk menampilkan hasil terbaik.',
                    '## Penilaian Hasil',
                    'Panen dinilai berdasarkan bobot dan kualitas jagung yang dihasilkan per luas lahan yang sama.',
                    '> Jagung unggulan menjadi andalan pangan dan bahan baku usaha rumahan di Desa Marga Agung.',
                    '## Penghargaan',
                    'Pemenang diberi benih unggulan dan alat pertanian sebagai bentuk apresiasi atas prestasi bertani.',
                ]),
            ],
            [
                'slug' => 'benih-jagung-unggulan',
                'judul' => 'Distribusi Benih Jagung Unggulan ke Kelompok Tani',
                'ringkasan' => 'Benih jagung unggulan dibagikan untuk meningkatkan produktivitas kebun para petani.',
                'kategori' => 'Pertanian',
                'tanggal' => '2026-10-20',
                'status' => StatusTerbit::Terbit,
                'isi' => implode("\n\n", [
                    'Pemerintah desa mendistribusikan benih jagung unggulan kepada delapan kelompok tani di seluruh dusun.',
                    '## Penerima Manfaat',
                    'Setiap kelompok tani menerima beras benih untuk ditanam di lahan cobalah sebelum disebarkan luas.',
                    '> Benih berkualitas adalah langkah awal menuju panen yang melimpah.',
                    '## Target Produktivitas',
                    'Dengan benih unggulan, diharapkan hasil panen jagung meningkat dan menjadi peluang usaha olahan.',
                ]),
            ],
            [
                'slug' => 'bibit-jagung-siap',
                'judul' => 'Bibit Jagung Siap Tanam Tersedia di Kantor Desa',
                'ringkasan' => 'Petani dapat mengambil bibit jagung berkualitas untuk musim tanam ini.',
                'kategori' => 'Pertanian',
                'tanggal' => '2026-09-25',
                'status' => StatusTerbit::Terbit,
                'isi' => implode("\n\n", [
                    'Kantor desa menyediakan bibit jagung siap tanam bagi petani yang ingin memulai musim tanam baru.',
                    '## Cara Pengambilan',
                    'Petani dapat datang ke kantor desa pada hari kerja untuk mengambil bibit sesuai kuota kelompok tani.',
                    '> Bibit jagung berkualitas bikin panen yang lebih baik.',
                    '## Jadwal Tanam',
                    'Petani diingatkan untuk menyesuaikan jadwal tanam dengan prakiraan cuaca di daerah masing-masing.',
                ]),
            ],
            [
                'slug' => 'panen-padi-dusun-tiga',
                'judul' => 'Musim Panen Padi Dimulai di Dusun Tiga',
                'ringkasan' => 'Petani mulai menuai padi sawah setelah musim hujan tiba di daerah.',
                'kategori' => 'Pertanian',
                'tanggal' => '2026-09-15',
                'status' => StatusTerbit::Terbit,
                'isi' => implode("\n\n", [
                    'Musim panen padi di Dusun Tiga dimulai setelah sawah menjadi kuning emas siap dianiaya.',
                    '## Aktivitas Panen',
                    'Petani bekerja mulai pagi untuk memanen padi dengan gotong royong bersama tetangga dan keluarga.',
                    '> Panen yang melimpah adalah buah kerja keras para petani selama bertahun-tahun.',
                    '## Pengolahan Hasil',
                    'Hasil panen disimpan di lumbung desa sebelum dijual atau dikonsumsi oleh warga.',
                ]),
            ],
            [
                'slug' => 'rapat-desa-november',
                'judul' => 'Rapat Pemerintahan Desa Bulan November',
                'ringkasan' => 'Perangkat desa membahas program pembangunan dan pelayanan warga untuk akhir tahun.',
                'kategori' => 'Pemerintahan',
                'tanggal' => '2026-10-25',
                'status' => StatusTerbit::Terbit,
                'isi' => implode("\n\n", [
                    'Rapat rutin pemerintah desa digelar untuk meninjau capaian program pembangunan setahun ini.',
                    '## Agenda Rapat',
                    'Pembahasan mencakup realisasi APBDes, jadwal pelayanan publik, dan program sosialisasi ke warga.',
                    '> Pelayanan prima kepada warga adalah prioritas utama pemerintah desa.',
                    '## Keputusan',
                    'Rapat menetapkan jadwal pelayanan kantor desa dan peningkatan koordinasi antar aparatur.',
                ]),
            ],
            [
                'slug' => 'surat-pengantar',
                'judul' => 'Prosedur Pengurusan Surat Pengantar Warga',
                'ringkasan' => 'Persyaratan dan cara mengajukan surat pengantar di kantor desa diperjelas.',
                'kategori' => 'Pemerintahan',
                'tanggal' => '2026-10-18',
                'status' => StatusTerbit::Terbit,
                'isi' => implode("\n\n", [
                    'Kantor desa memberikan informasi mengenai tata cara pengurusan surat pengantar untuk keperluan warga.',
                    '## Persyaratan',
                    'Warga membawa KTP, KK, dan berkas pendukung sesuai jenis surat pengantar yang diajukan.',
                    '> Surat pengantar diterbitkan setelah verifikasi data warga oleh aparat desa yang berwenang.',
                    '## Estimasi Waktu',
                    'Pengurusan surat pengantar selesai dalam waktu tiga hari kerja setelah berkas lengkap diserahkan.',
                ]),
            ],
            [
                'slug' => 'jaringan-pemerintahan',
                'judul' => 'Jaringan Pemerintahan Desa Ditingkatkan',
                'ringkasan' => 'Kolaborasi antar aparat desa untuk pelayanan yang lebih cepat dan tepat.',
                'kategori' => 'Pemerintahan',
                'tanggal' => '2026-09-08',
                'status' => StatusTerbit::Draf,
                'isi' => implode("\n\n", [
                    'Pemerintah desa sedang menyusun rencana peningkatan jaringan komunikasi antar aparatur desa.',
                    '## Tujuan Peningkatan',
                    'Kolaborasi yang lebih baik diharapkan menghasilkan pelayanan publik yang lebih cepat dan terpadu.',
                    '> Jaringan yang terjalin baik akan mempercepat respons terhadap kebutuhan warga.',
                    '## Tahap Pelaksanaan',
                    'Rencana masih dalam tahap penyusunan dan akan dibahas dalam rapat pemerintah desa mendatang.',
                ]),
            ],
            [
                'slug' => 'libur-sekolah',
                'judul' => 'Pengumuman Jadwal Libur Sekolah Akhir Tahun',
                'ringkasan' => 'Libur sekolah awal Desember disesuaikan dengan kalender pendidikan resmi.',
                'kategori' => 'Pengumuman',
                'tanggal' => '2026-10-22',
                'status' => StatusTerbit::Terbit,
                'isi' => implode("\n\n", [
                    'Pemerintah desa mengumumkan jadwal libur sekolah akhir tahun bagi satuan pendidikan di daerah.',
                    '## Jadwal Libur',
                    'Libur dimulai awal Desember dan disesuaikan dengan kalender pendidikan yang telah ditetapkan.',
                    '> Warga diminta untuk memperhatikan jadwal libur dalam merencanakan kegiatan anak-anak.',
                    '## Keterangan',
                    'Informasi lebih lanjut dapat ditanyakan langsung ke sekolah atau kantor desa terdekat.',
                ]),
            ],
            [
                'slug' => 'padam-listrik',
                'judul' => 'Pengumuman Pemadaman Listrik Terjadwal',
                'ringkasan' => 'Listrik akan padam untuk pemeliharaan jaringan di beberapa dusun desa.',
                'kategori' => 'Pengumuman',
                'tanggal' => '2026-10-05',
                'status' => StatusTerbit::Terbit,
                'isi' => implode("\n\n", [
                    'Perusahaan listrik akan melakukan pemadaman listrik terjadwal untuk pemeliharaan jaringan.',
                    '## Dusun yang Terlibat',
                    'Pemadaman berlaku di beberapa dusun pada hari dan jam tertentu yang akan diumumkan kembali.',
                    '> Warga disarankan untuk menyiapkan lampu penerangan darurat sebelum waktu pemadaman.',
                    '## Pemulihan',
                    'Listrik akan diaktifkan kembali segera setelah pekerjaan pemeliharaan selesai dilakukan.',
                ]),
            ],
            [
                'slug' => 'festival-draf',
                'judul' => 'Draf Pengumuman Festival Desa',
                'ringkasan' => 'Naskah pengumuman festival desa masih disunting oleh panitia sebelum diterbitkan.',
                'kategori' => 'Pengumuman',
                'tanggal' => '2026-11-01',
                'status' => StatusTerbit::Draf,
                'isi' => implode("\n\n", [
                    'Panitia sedang menyiapkan naskah pengumuman festival desa tahun ini.',
                    '## Konten Pengumuman',
                    'Naskah mencakup jadwal acara, lokasi pelaksanaan, dan undangan bagi warga serta tamu.',
                    '> Pengumuman resmi akan diterbitkan setelah naskah disetujui oleh kepala desa.',
                    '## Status',
                    'Data ini masih draf dan belum ditampilkan kepada masyarakat.',
                ]),
            ],
            [
                'slug' => 'kegiatan-draf',
                'judul' => 'Draf Berita Kegiatan Bulanan',
                'ringkasan' => 'Rencana kegiatan desa untuk bulan depan belum final dan belum diverifikasi.',
                'kategori' => 'Kegiatan',
                'tanggal' => '2026-11-03',
                'status' => StatusTerbit::Draf,
                'isi' => implode("\n\n", [
                    'Rencana kegiatan desa untuk bulan depan masih dalam penyusunan oleh aparat terkait.',
                    '## Rencana Kegiatan',
                    'Beberapa kegiatan calon akan disesuaikan dengan anggaran dan jadwal yang tersedia.',
                    '> Berita ini masih draf dan belum siap diterbitkan untuk warga.',
                    '## Verifikasi',
                    'Data akan diperbarui setelah rencana kegiatan divalidasi oleh pemerintah desa.',
                ]),
            ],
        ];

        foreach ($data as $row) {
            Berita::query()->updateOrCreate(['slug' => $row['slug']], [
                'judul' => $row['judul'],
                'kategori' => $row['kategori'],
                'ringkasan' => $row['ringkasan'],
                'isi' => $row['isi'],
                'tanggal' => $row['tanggal'],
                'status' => $row['status'],
                'diterbitkan_at' => $row['status'] === StatusTerbit::Terbit ? $row['tanggal'] : null,
                'contoh' => true,
            ]);
        }
    }

    private function agenda(): void
    {
        $data = [
            // [judul, tanggal, waktu, lokasi, status]
            ['Musyawarah Desa', now()->addDays(2)->toDateString(), '09:00', 'Balai Desa', StatusTerbit::Terbit],
            ['Posyandu', now()->addDays(9)->toDateString(), '08:00', null, StatusTerbit::Terbit],
            ['Kerja bakti', now()->addDays(16)->toDateString(), null, null, StatusTerbit::Terbit],
            ['Penyuluhan pertanian', now()->addDays(23)->toDateString(), '13:00', 'Lapangan desa', StatusTerbit::Terbit],
            ['Kejagungan', now()->addDays(30)->toDateString(), null, 'Balai Desa', StatusTerbit::Terbit],
            ['Rapim', now()->subDays(5)->toDateString(), '08:00', 'Dusun Satu', StatusTerbit::Terbit],
            ['Posyandu', now()->subDays(12)->toDateString(), '09:00', null, StatusTerbit::Terbit],
            ['Gotong royong', now()->subDays(20)->toDateString(), null, null, StatusTerbit::Terbit],
            ['Komsos', now()->subDays(30)->toDateString(), '16:00', 'Dusun Tiga', StatusTerbit::Terbit],
            ['Rapat draf agenda', now()->addDays(45)->toDateString(), null, null, StatusTerbit::Draf],
        ];

        foreach ($data as [$judul, $tanggal, $waktu, $lokasi, $status]) {
            Agenda::query()->updateOrCreate(
                ['judul' => $judul, 'tanggal' => $tanggal],
                ['waktu' => $waktu, 'lokasi' => $lokasi, 'status' => $status, 'contoh' => true],
            );
        }
    }

    private function perangkat(): void
    {
        $data = [
            // [nama, urutan, tampil]
            'Slamet Riyanto' => true,
            'Siti Rahayu' => true,
            'Bambang Sutrisno' => true,
            'Rina Marlina' => false, // disembunyikan
            'Joko Prakoso' => true,
            'Dewi Sartika' => true,
            'Agus Setiawan' => true,
            'Maya Lestari' => true,
        ];

        foreach (Jabatan::query()->orderBy('urutan')->get() as $index => $jabatan) {
            $nama = array_keys($data)[$index] ?? 'Nama Perangkat '.$jabatan->nama;
            $tampil = $data[$nama] ?? true;

            PerangkatDesa::query()->updateOrCreate(
                ['jabatan_id' => $jabatan->id],
                ['nama' => $nama, 'urutan' => $jabatan->urutan, 'tampil' => $tampil, 'contoh' => true],
            );
        }
    }

    private function potensi(): void
    {
        $komoditas = [
            ['nama' => 'Jagung', 'unggulan' => true, 'status' => StatusTerbit::Terbit, 'deskripsi' => 'Komoditas utama dan produk unggulan Desa Marga Agung. Jagung dapat menjadi bahan pangan dan bahan baku usaha rumahan dengan nilai tambah.', 'musim_panen' => 'Mar–Mei & Sep–Nov'],
            ['nama' => 'Singkong', 'unggulan' => false, 'status' => StatusTerbit::Terbit, 'deskripsi' => 'Singkong diolah menjadi berbagai produk keripik dan tepung untuk kebutuhan lokal.', 'musim_panen' => 'Sep–Des'],
            ['nama' => 'Pisang', 'unggulan' => false, 'status' => StatusTerbit::Terbit, 'deskripsi' => 'Pisang tumbuh subur di seluruh dusun dan dijual segar atau diolah menjadi chips.', 'musim_panen' => 'Seluruh tahun'],
            ['nama' => 'Padi', 'unggulan' => false, 'status' => StatusTerbit::Terbit, 'deskripsi' => 'Sawah padi di Dusun Tiga menjadi sumber pangan utama warga setiap musim panen.', 'musim_panen' => 'Apr–Jun'],
            ['nama' => 'Kakao', 'unggulan' => false, 'status' => StatusTerbit::Draf, 'deskripsi' => 'Tanaman kakao mulai dikembangkan sebagai komoditas perkebunan potensial di desa.', 'musim_panen' => 'Mar–Jun'],
        ];

        foreach ($komoditas as $row) {
            Komoditas::query()->updateOrCreate(['nama' => $row['nama']], [
                'deskripsi' => $row['deskripsi'],
                'musim_panen' => $row['musim_panen'],
                'unggulan' => $row['unggulan'],
                'status' => $row['status'],
                'contoh' => true,
            ]);
        }

        $jagung = Komoditas::query()->where('nama', 'Jagung')->first();
        $dusun = fn (int $i) => Dusun::query()->find($i);

        $kelompok = [
            ['nama' => 'Kelompok Tani Sumber Rejeki', 'komoditas' => 'Jagung', 'dusun' => 1],
            ['nama' => 'Kelompok Tani Tani Makmur', 'komoditas' => 'Jagung', 'dusun' => 2],
            ['nama' => 'Kelompok Tani Anak Bangsa', 'komoditas' => 'Singkong', 'dusun' => 3],
            ['nama' => 'Kelompok Tani Pisang Hijau', 'komoditas' => 'Pisang', 'dusun' => 4],
            ['nama' => 'Kelompok Tani Sawah Abadi', 'komoditas' => 'Padi', 'dusun' => 5],
            ['nama' => 'Kelompok Tani Cokelat Nusantara', 'komoditas' => 'Kakao', 'dusun' => 6],
            ['nama' => 'Kelompok Tani Baru Sejahtera', 'komoditas' => 'Singkong', 'dusun' => 1],
            ['nama' => 'Kelompok Tani Jagung Jaya', 'komoditas' => 'Jagung', 'dusun' => 3],
        ];

        foreach ($kelompok as $row) {
            $komoditasId = Komoditas::query()->where('nama', $row['komoditas'])->value('id');

            KelompokTani::query()->updateOrCreate(['nama' => $row['nama']], [
                'komoditas_id' => $komoditasId,
                'dusun_id' => $dusun($row['dusun'])->id,
                'ketua' => null,
                'kontak' => null,
                'kontak_disetujui' => false,
                'contoh' => true,
            ]);
        }

        $umkm = [
            ['nama' => 'Dapur Jagung Bersama', 'produk' => 'Keripik jagung renyah', 'deskripsi' => 'Camilan olahan jagung untuk teman minum teh dan bingkisan keluarga.', 'whatsapp' => '081200000001', 'whatsapp_disetujui' => true, 'status' => StatusTerbit::Terbit],
            ['nama' => 'Anyaman Sahabat Desa', 'produk' => 'Keranjang & wadah anyaman', 'deskripsi' => 'Kerajinan anyaman untuk kebutuhan rumah dan kemasan hadiah.', 'whatsapp' => null, 'whatsapp_disetujui' => false, 'status' => StatusTerbit::Terbit],
            ['nama' => 'Keripik Singkong Cahaya', 'produk' => 'Keripik singkong rasa asli', 'deskripsi' => 'Keripik singkong dari singkong lokal yang digoreng renyah.', 'whatsapp' => null, 'whatsapp_disetujui' => false, 'status' => StatusTerbit::Terbit],
            ['nama' => 'Ternak Kambing Sejahtera', 'produk' => 'Daging kambing & susu', 'deskripsi' => 'Hasil ternak kambing untuk konsumsi lokal dan idul adha.', 'whatsapp' => null, 'whatsapp_disetujui' => false, 'status' => StatusTerbit::Terbit],
            ['nama' => 'Olahan Pisang Kering', 'produk' => 'Pisang kering & chips', 'deskripsi' => 'Pisang dikeringkan menjadi cemilan awet dan bergizi.', 'whatsapp' => null, 'whatsapp_disetujui' => false, 'status' => StatusTerbit::Terbit],
            ['nama' => 'Madu Hutan Desa', 'produk' => 'Madu murni dari lebah', 'deskripsi' => 'Madu hasil panen lebah hutan tanpa campuran.', 'whatsapp' => null, 'whatsapp_disetujui' => false, 'status' => StatusTerbit::Terbit],
            ['nama' => 'Kerupuk Ikan Nusantara', 'produk' => 'Kerupuk ikan gurih', 'deskripsi' => 'Kerupuk dari ikan air tawar dengan bumbu tradisional.', 'whatsapp' => null, 'whatsapp_disetujui' => false, 'status' => StatusTerbit::Terbit],
            ['nama' => 'Tahu Tempe Makmur', 'produk' => 'Tahu & tempe segar', 'deskripsi' => 'Tahu dan tempe homemade untuk lauk harian warga.', 'whatsapp' => null, 'whatsapp_disetujui' => false, 'status' => StatusTerbit::Draf],
            ['nama' => 'Sambal Bu Rina', 'produk' => 'Sambal cabe & tomat', 'deskripsi' => 'Sambal homemade untuk pelengkap masakan sehari-hari.', 'whatsapp' => null, 'whatsapp_disetujui' => false, 'status' => StatusTerbit::Draf],
            ['nama' => 'Anyaman Bambu Kencana', 'produk' => 'Tas & tikar anyaman', 'deskripsi' => 'Produk anyaman bambu untuk hiasan dan kerajinan.', 'whatsapp' => '081200000002', 'whatsapp_disetujui' => true, 'status' => StatusTerbit::Terbit],
        ];

        foreach ($umkm as $row) {
            Umkm::query()->updateOrCreate(['nama' => $row['nama']], [
                'produk' => $row['produk'],
                'deskripsi' => $row['deskripsi'],
                'whatsapp' => $row['whatsapp'],
                'whatsapp_disetujui' => $row['whatsapp_disetujui'],
                'status' => $row['status'],
                'contoh' => true,
            ]);
        }
    }

    private function apbdes(): void
    {
        $pendapatan = [
            'Pendapatan Asli Desa',
            'Dana Desa',
            'Alokasi Dana Desa',
            'Bagi Hasil Pajak & Retribusi',
            'Bantuan Keuangan',
            'Lain-lain Pendapatan yang Sah',
        ];

        $belanja = [
            'Penyelenggaraan Pemerintahan Desa',
            'Pelaksanaan Pembangunan Desa',
            'Pembinaan Kemasyarakatan',
            'Pemberdayaan Masyarakat',
            'Penanggulangan Bencana, Darurat & Mendesak',
        ];

        $tahun = [
            2026 => [
                'pendapatan' => [150_000_000, 1_600_000_000, 300_000_000, 100_000_000, 50_000_000, 50_000_000],
                'belanja' => [400_000_000, 800_000_000, 200_000_000, 150_000_000, 100_000_000],
            ],
            2025 => [
                'pendapatan' => [130_000_000, 1_400_000_000, 250_000_000, 90_000_000, 40_000_000, 40_000_000],
                'belanja' => [350_000_000, 750_000_000, 180_000_000, 130_000_000, 90_000_000],
            ],
            2024 => [
                'pendapatan' => [120_000_000, 1_300_000_000, 200_000_000, 80_000_000, 30_000_000, 30_000_000],
                'belanja' => [320_000_000, 700_000_000, 160_000_000, 120_000_000, 80_000_000],
            ],
        ];

        foreach ($tahun as $tahunValue => $anggaran) {
            $periode = ApbdesPeriode::query()->updateOrCreate(
                ['tahun' => $tahunValue],
                ['periode' => "1 Januari – 31 Desember {$tahunValue}", 'status' => StatusTerbit::Terbit, 'diterbitkan_at' => now(), 'contoh' => true],
            );

            $totalPendapatan = array_sum($anggaran['pendapatan']);
            $totalBelanja = array_sum($anggaran['belanja']);
            $pembiayaan = $totalPendapatan - $totalBelanja;

            foreach ($pendapatan as $urutan => $uraian) {
                ApbdesRincian::query()->updateOrCreate(
                    ['periode_id' => $periode->id, 'jenis' => JenisAnggaran::Pendapatan, 'uraian' => $uraian],
                    ['anggaran' => $anggaran['pendapatan'][$urutan], 'urutan' => $urutan + 1],
                );
            }

            foreach ($belanja as $urutan => $uraian) {
                ApbdesRincian::query()->updateOrCreate(
                    ['periode_id' => $periode->id, 'jenis' => JenisAnggaran::Belanja, 'uraian' => $uraian],
                    ['anggaran' => $anggaran['belanja'][$urutan], 'urutan' => $urutan + 1],
                );
            }

            ApbdesRincian::query()->updateOrCreate(
                ['periode_id' => $periode->id, 'jenis' => JenisAnggaran::Pembiayaan, 'uraian' => 'Pembiayaan netto'],
                ['anggaran' => $pembiayaan, 'urutan' => 1],
            );
        }

        // Dokumen contoh 2026 (path null) sudah ada dari DataContohSeeder, dipertahankan.
        // Tambahkan 1 dokumen untuk 2025 (path null).
        $periode2025 = ApbdesPeriode::query()->where('tahun', 2025)->first();
        $periode2025?->dokumen()->firstOrCreate(
            ['judul' => 'Peraturan Desa tentang APBDes Tahun 2025'],
            ['keterangan_periode' => 'Jan–Des 2025'],
        );
    }

    private function aspirasi(): void
    {
        $data = [
            // [nama, pesan, waktu, status]
            ['Ahmad Samsu', 'Mohon perbaikan jalan dusun yang rusak setelah hujan.', now()->subDays(2)->setHour(10)->setMinute(30), StatusAspirasi::Baru],
            ['Siti Marni', 'Mohon jadwal posyandu bulan depan.', now()->subDays(5)->setHour(8)->setMinute(15), StatusAspirasi::Baru],
            ['Bambang Hadi', 'Mohon bantuan bibit jagung untuk musim panen.', now()->subDays(9)->setHour(14)->setMinute(0), StatusAspirasi::Baru],
            ['Dewi Lestari', 'Lampu jalan di depan dusun sudah padam tiga hari.', now()->subDays(12)->setHour(19)->setMinute(45), StatusAspirasi::Baru],
            ['Rina Marlina', 'Mohon surat pengantar untuk pengajuan KIP.', now()->subDays(15)->setHour(9)->setMinute(0), StatusAspirasi::Dibaca],
            ['Agus Setiawan', 'Permohonan bantuan anak goyah.', now()->subDays(18)->setHour(16)->setMinute(20), StatusAspirasi::Dibaca],
            ['Maya Sari', 'Jalan rusak parah di dekat sungai perlu diperbaiki.', now()->subDays(22)->setHour(11)->setMinute(10), StatusAspirasi::Dibaca],
            ['Joko Prasetyo', 'Mohon informasi pendaftaran PKL di kantor desa.', now()->subDays(25)->setHour(13)->setMinute(40), StatusAspirasi::Dibaca],
            ['Slamet Riyanto', 'Berkas pengurusan akta sudah lengkap.', now()->subDays(28)->setHour(8)->setMinute(30), StatusAspirasi::Selesai],
            ['Siti Rahayu', 'Aduan air PAM tidak mau keluar di rumah.', now()->subDays(14)->setHour(15)->setMinute(0), StatusAspirasi::Selesai],
            ['Rina Wulandari', 'Permohonan penyaluran pupuk untuk petani.', now()->subDays(20)->setHour(10)->setMinute(15), StatusAspirasi::Selesai],
            ['Budi Santoso', 'Laporan kerusakan jembatan kecil di jalan dusun.', now()->subDays(30)->setHour(16)->setMinute(50), StatusAspirasi::Selesai],
        ];

        foreach ($data as [$nama, $pesan, $waktu, $status]) {
            $aspirasi = Aspirasi::query()->updateOrCreate(['pesan' => $pesan], [
                'nama' => $nama,
                'status' => $status,
                'dibaca_at' => $status === StatusAspirasi::Baru ? null : $waktu,
                'selesai_at' => $status === StatusAspirasi::Selesai ? $waktu : null,
                'contoh' => true,
            ]);

            $aspirasi->forceFill(['created_at' => $waktu, 'updated_at' => $waktu])->save();

            // Riwayat sesuai status (diterima → dibaca → selesai). Hapus yang lama agar idempoten.
            $aspirasi->riwayat()->delete();

            $aksi = [];
            if ($status !== StatusAspirasi::Baru) {
                $aksi[] = 'diterima';
            }
            if ($status === StatusAspirasi::Dibaca || $status === StatusAspirasi::Selesai) {
                $aksi[] = 'dibaca';
            }
            if ($status === StatusAspirasi::Selesai) {
                $aksi[] = 'selesai';
            }

            if ($aksi === []) {
                $aksi = ['diterima'];
            }

            $tanggalRiwayat = clone $waktu;
            foreach ($aksi as $index => $namaAksi) {
                $aspirasi->riwayat()->create([
                    'user_id' => null,
                    'aksi' => $namaAksi,
                    'catatan' => null,
                ])->forceFill(['created_at' => $tanggalRiwayat->copy()->addMinutes($index * 30)])->save();
            }
        }
    }
}
