# Website Profil dan Informasi Desa Marga Agung

Website profil dan informasi Desa Marga Agung: profil desa, transparansi APBDes, potensi pertanian dan UMKM, berita dan agenda, serta ruang aspirasi warga, dilengkapi dashboard admin bagi perangkat desa.

**Status:** tahap perencanaan (SKPL). Proyek Laravel sudah dibuat, fitur belum dikembangkan.

## Teknologi

| Komponen | Spesifikasi |
|---|---|
| Backend | PHP 8.x; gunakan versi yang masih didukung dan kompatibel dengan framework |
| Framework backend | Laravel 10 atau 11 sesuai kompatibilitas dependensi dan dukungan keamanan saat pengembangan; verifikasi versi Laravel yang masih mendapat dukungan sebelum menetapkan versi final |
| Basis data | MySQL / MariaDB |
| Frontend | HTML5, CSS, dan JavaScript dengan Tailwind CSS (pilihan utama; alternatif Bootstrap, pilih satu pendekatan utama agar desain konsisten) |
| Web server | Apache atau Nginx, disesuaikan dengan konfigurasi hosting |
| Versi kode | Git |
| Penyimpanan dokumen | Dokumen PDF dan gambar diunggah melalui validasi tipe dan ukuran berkas; nama berkas dan lokasi penyimpanan dikelola dengan aman |

## Kebutuhan Fungsional

### Modul Publik

| ID | Fitur | Keterangan |
|---|---|---|
| FR-01 | Beranda | Berita terbaru, jumlah penduduk, potensi unggulan, dan tautan cepat menuju informasi APBDes |
| FR-02 | Profil Desa | Sejarah, visi dan misi, struktur organisasi pemerintah desa (nama, foto, dan jabatan aparat), serta letak geografis desa |
| FR-03 | Transparansi APBDes | Ringkasan pendapatan dan belanja dalam bentuk tabel dan/atau diagram, serta unduhan dokumen APBDes PDF per tahun |
| FR-04 | Potensi Desa | Katalog komoditas pertanian (termasuk informasi musim panen bila tersedia dan kontak kelompok tani) dan UMKM (nama usaha, produk, foto, dan nomor WhatsApp penjual) |
| FR-05 | Berita dan Agenda | Daftar berita atau artikel kegiatan desa, halaman detail berita, dan agenda kegiatan mendatang |
| FR-06 | Ruang Aspirasi | Formulir pertanyaan, keluhan, dan saran (nama dan isi pesan wajib diisi; NIK opsional); masukan divalidasi sebelum disimpan |

### Modul Admin (Dashboard Perangkat Desa)

| ID | Fitur | Keterangan |
|---|---|---|
| FR-07 | Autentikasi Admin | Login dengan email atau username dan kata sandi untuk pengguna yang memiliki akun admin |
| FR-08 | Manajemen Profil | Perbarui sejarah, visi dan misi, serta struktur organisasi termasuk nama, jabatan, dan foto aparat desa |
| FR-09 | Manajemen APBDes | Unggah dokumen APBDes PDF, pilih tahun/periode, dan masukkan ringkasan pendapatan serta belanja untuk ditampilkan dalam tabel atau diagram |
| FR-10 | Potensi dan UMKM | CRUD data kelompok tani, komoditas, dan UMKM, termasuk foto produk dan informasi kontak |
| FR-11 | Manajemen Berita dan Agenda | CRUD berita dan agenda, termasuk mengelola teks, gambar, tanggal, dan status publikasi |
| FR-12 | Manajemen Aspirasi | Lihat aspirasi yang masuk, perbarui status menjadi 'Sudah Dibaca' atau 'Selesai', dan tulis balasan bila diperlukan |

Peran pengguna: **Super Admin** dan **Staf** (sekurang-kurangnya keduanya, sesuai kewenangan yang disepakati).

## Kebutuhan Non-Fungsional

| ID | Aspek | Target |
|---|---|---|
| NFR-01 | Kinerja | Halaman utama termuat dalam waktu kurang dari 3 detik pada kondisi uji yang ditetapkan |
| NFR-02 | Keamanan | Kata sandi di-hash (misalnya bcrypt atau Argon2), validasi input, perlindungan terhadap SQL injection dan XSS, dan batas percobaan login yang sesuai; gambar dan aset statis dioptimalkan serta dapat menggunakan caching |
| NFR-03 | Kontrol akses | Hak akses dibedakan berdasarkan peran (Super Admin dan Staf) sesuai kewenangan yang disepakati |
| NFR-04 | Kegunaan | Antarmuka responsif dengan prioritas tampilan smartphone, bahasa Indonesia yang baku, navigasi konsisten, dan ukuran teks nyaman (dasar 14 px sebagai target awal) |
| NFR-05 | Ketersediaan | Layanan hosting ditargetkan uptime minimal 99% per bulan, sesuai kemampuan penyedia hosting dan pemantauan yang tersedia |
| NFR-06 | Kompatibilitas | Website dapat digunakan melalui peramban modern pada perangkat desktop dan smartphone |
| NFR-07 | Pemeliharaan | Kode, dependensi, dan konfigurasi dikelola secara teratur; database dan dokumen penting dicadangkan secara berkala dengan akses pemulihan yang dibatasi |

## Kebutuhan Data

| ID | Data | Penanggung jawab |
|---|---|---|
| DR-01 | Profil desa | Pemerintah desa |
| DR-02 | Keuangan (APBDes PDF minimal 2 tahun terakhir dan angka ringkasan) | Pemerintah desa / keuangan |
| DR-03 | Potensi pertanian | Kelompok tani / perangkat desa |
| DR-04 | UMKM (target awal minimal 10 usaha) | Pelaku UMKM / perangkat desa |
| DR-05 | Berita dan agenda | Perangkat desa |
| DR-06 | Aspirasi | Warga dan petugas pengelola |
| DR-07 | Kontak dan lokasi | Pemerintah desa |

## Catatan

- NIK pada formulir aspirasi bersifat opsional dan sebaiknya tidak dikumpulkan kecuali benar-benar diperlukan. Jika dikumpulkan, desa perlu menetapkan tujuan, pembatasan akses, masa penyimpanan, dan perlindungan data pribadi yang jelas.
- Kata sandi di-hash (bukan dienkripsi): tidak disimpan sebagai teks asli dan tidak perlu dapat didekripsi.
- Unggahan PDF dan gambar divalidasi tipe dan ukurannya.
- Pihak yang berwenang menyetujui publikasi (APBDes, profil, foto aparat desa, kontak UMKM, balasan aspirasi) perlu dikonfirmasi sebelum pengembangan.

## Tim

| Nama | Peran | Tanggung Jawab |
|---|---|---|
| Baginda | UI/UX Designer | Membuat wireframe dan prototipe mobile-first; memastikan menu APBDes dan Berita dapat dijangkau maksimal dua langkah dari beranda |
| Bayu | Backend Developer | Mengembangkan autentikasi, kontrol akses, validasi, pengelolaan basis data, dan CRUD modul admin; menerapkan pembatasan spam pada formulir aspirasi |
| Faathir | Frontend Developer | Menerapkan desain responsif dengan Tailwind CSS, mengintegrasikan halaman dengan backend, dan mengoptimalkan ukuran gambar |
| Angelica | Software Tester | Menyusun dan menjalankan skenario pengujian fungsional, responsivitas, validasi formulir, kontrol akses, keamanan input, dan performa pada simulasi jaringan lambat |
