# DESIGN.md — Website Profil dan Informasi Desa Marga Agung

> Dokumen acuan desain UI/UX dan implementasi antarmuka. Disusun berdasarkan SKPL *Website Profil dan Informasi Desa Marga Agung*. Detail visual yang tidak ditentukan oleh SKPL ditetapkan sebagai rekomendasi desain dan dapat dikonfirmasi bersama perangkat desa.

## 1. Tujuan Produk

Membangun website desa yang informatif, mudah digunakan, responsif, dan tepercaya untuk:
- Menyediakan informasi resmi mengenai profil dan kegiatan Desa Marga Agung.
- Menampilkan transparansi APBDes melalui ringkasan dan dokumen PDF.
- Memperkenalkan potensi pertanian dan UMKM desa.
- Memudahkan warga menyampaikan pertanyaan, keluhan, atau saran.
- Memberi perangkat desa sarana untuk mengelola konten melalui dashboard admin.

**Prinsip utama desain:** mengutamakan kebutuhan warga, mobile-first, navigasi konsisten, keterbacaan, transparansi, dan akses admin yang aman.

## 2. Dasar Kebutuhan dari SKPL

Implementasi desain wajib mendukung kebutuhan berikut.

| ID SKPL | Kebutuhan desain |
|---|---|
| FR-01 | Beranda menampilkan berita terbaru, jumlah penduduk, potensi unggulan, dan tautan cepat APBDes. |
| FR-02 | Profil memuat sejarah, visi-misi, struktur organisasi (nama, jabatan, foto), serta letak geografis. |
| FR-03 | APBDes menampilkan ringkasan pendapatan dan belanja dalam tabel dan/atau diagram, serta PDF yang bisa diunduh per tahun. |
| FR-04 | Potensi desa menyediakan katalog pertanian dan UMKM; informasi musim panen jika tersedia; kontak kelompok tani; foto produk dan WhatsApp penjual jika disetujui. |
| FR-05 | Berita memiliki daftar dan halaman detail; agenda mendatang dapat dilihat. |
| FR-06 | Ruang Aspirasi menyediakan formulir nama dan pesan, dengan NIK opsional, validasi, serta pesan kesalahan yang jelas. |
| FR-07 | Login admin menggunakan email/username dan kata sandi. |
| FR-08 | Admin berwenang mengelola profil dan struktur perangkat desa. |
| FR-09 | Admin berwenang mengelola dokumen dan ringkasan APBDes per periode. |
| FR-10 | Admin berwenang melakukan CRUD data pertanian, kelompok tani, dan UMKM. |
| FR-11 | Admin berwenang mengelola berita dan agenda, termasuk gambar, tanggal, dan status draf/publikasi. |
| FR-12 | Admin dapat melihat aspirasi, mengubah status menjadi “Sudah Dibaca” atau “Selesai”, dan mencatat balasan bila digunakan. |
| NFR-01 | Target halaman utama dimuat kurang dari 3 detik pada kondisi pengujian yang disepakati. |
| NFR-02 | Antarmuka mendukung validasi input, unggahan aman, serta alur login yang aman; keamanan harus ditegakkan di backend. |
| NFR-03 | Akses antarmuka dibedakan menurut peran minimal Super Admin dan Staf. |
| NFR-04 | Responsif dengan prioritas smartphone, Bahasa Indonesia baku, navigasi konsisten, teks dasar target awal 14 px. |
| NFR-05 | Target ketersediaan hosting 99% per bulan bergantung pada penyedia dan pemantauan. |
| NFR-06 | Mendukung peramban modern desktop dan smartphone. |
| NFR-07 | Konten dan berkas penting mendukung prosedur backup/pemulihan; ini terutama tanggung jawab operasional. |

## 3. Pengguna dan Hak Akses

### 3.1 Pengunjung umum
- Mengakses beranda, profil, APBDes, potensi desa, berita, dan agenda.
- Mengunduh dokumen APBDes.
- Membuka kontak publik yang telah disetujui.
- Tidak perlu login untuk membaca informasi publik.

### 3.2 Warga
- Semua kemampuan pengunjung umum.
- Mengirim aspirasi melalui formulir.
- NIK opsional; jangan meminta atau menampilkan data yang tidak dibutuhkan.

### 3.3 Staf
- Login ke dashboard.
- Mengelola hanya modul yang diizinkan oleh kewenangannya.
- Melihat dan memproses aspirasi sesuai hak akses.

### 3.4 Super Admin
- Memiliki akses administrasi lebih luas daripada Staf.
- Mengelola akun/otorisasi sesuai kebijakan yang ditetapkan.
- Tindakan sensitif perlu dibatasi dan dapat diaudit.

> Matriks izin final harus dikonfirmasi dengan pemerintah desa. Menyembunyikan tombol di UI bukan pengganti pemeriksaan izin di server.

## 4. Peta Situs dan Navigasi

### 4.1 Situs publik

```text
Website Desa Marga Agung
├── Beranda
├── Profil Desa
│   ├── Sejarah Desa
│   ├── Visi dan Misi
│   ├── Struktur Pemerintahan
│   └── Lokasi dan Geografis
├── Transparansi APBDes
│   ├── Ringkasan Pendapatan dan Belanja
│   └── Arsip Dokumen PDF per Tahun
├── Potensi Desa
│   ├── Pertanian dan Kelompok Tani
│   └── UMKM
├── Berita dan Agenda
│   ├── Daftar Berita
│   ├── Detail Berita
│   └── Agenda Mendatang
├── Ruang Aspirasi
└── Kontak dan Lokasi
```

### 4.2 Dashboard admin

```text
Dashboard Admin
├── Ringkasan
├── Profil Desa
├── Struktur Perangkat Desa
├── APBDes
├── Potensi Pertanian
├── Kelompok Tani
├── UMKM
├── Berita
├── Agenda
├── Aspirasi Warga
└── Pengaturan Akun dan Hak Akses (sesuai kewenangan)
```

### 4.3 Aturan navigasi
- Pada desktop, gunakan header dengan identitas desa dan navigasi utama.
- Pada smartphone, gunakan menu ringkas yang mudah dioperasikan dengan satu tangan.
- Menu **APBDes** dan **Berita** harus dapat dijangkau maksimal dua langkah dari beranda, sesuai SKPL.
- Tautan aktif harus memiliki penanda visual yang jelas.
- Breadcrumb digunakan pada halaman bertingkat seperti detail berita dan halaman edit admin.
- Sediakan footer berisi kontak resmi, alamat, tautan penting, dan informasi hak cipta jika datanya tersedia.

## 5. Arah Visual dan Design System

Arah visual yang disarankan: **resmi, ramah, bersih, membumi, dan modern**. Hindari tampilan seperti template dashboard generik atau dekorasi berlebihan. Gunakan identitas resmi desa jika tersedia.

### 5.1 Warna

Palet awal berikut adalah rekomendasi desain, bukan ketentuan eksplisit SKPL. Sesuaikan setelah logo/identitas visual resmi dikonfirmasi.

| Token | Nilai | Penggunaan |
|---|---|---|
| `--color-primary` | `#1F6B45` | Tombol utama, tautan aktif, elemen identitas |
| `--color-primary-dark` | `#174C33` | Hover/active dan header tertentu |
| `--color-primary-soft` | `#EAF4ED` | Latar lembut untuk highlight |
| `--color-accent` | `#D9A441` | Aksen kecil dan penanda penting |
| `--color-text` | `#1F2937` | Teks utama |
| `--color-muted` | `#667085` | Teks sekunder |
| `--color-border` | `#E5E7EB` | Garis pemisah dan border |
| `--color-surface` | `#FFFFFF` | Kartu dan permukaan utama |
| `--color-background` | `#F7F8F6` | Latar halaman |
| `--color-success` | `#237A4B` | Status berhasil |
| `--color-warning` | `#B7791F` | Peringatan |
| `--color-danger` | `#B42318` | Kesalahan dan tindakan destruktif |

Pastikan kontras teks memadai. Jangan mengandalkan warna saja untuk membedakan status.

### 5.2 Tipografi
- Gunakan font sans-serif yang mudah dibaca; rekomendasi: **Inter** atau font sistem yang setara.
- Teks isi: `16px` direkomendasikan; batas awal SKPL adalah 14 px.
- Label, metadata, dan helper text: minimal nyaman dibaca pada layar kecil; hindari teks penting terlalu kecil.
- Judul halaman: 28–36 px desktop, 24–30 px smartphone.
- Tinggi baris teks isi: sekitar 1.5–1.7.
- Gunakan bobot font secara konsisten: 400 untuk isi, 500–600 untuk label/subjudul, 650–700 untuk judul.

### 5.3 Grid, jarak, dan bentuk
- Lebar konten maksimum rekomendasi: `1200px`.
- Padding horizontal: 24 px desktop, 16 px tablet, 16 px smartphone.
- Gunakan sistem jarak berbasis kelipatan 4 atau 8 px.
- Radius kartu: 12–16 px; radius input/tombol: 8–10 px.
- Gunakan bayangan tipis hanya untuk elemen yang perlu elevasi.
- Utamakan ruang kosong dan hierarki yang jelas, bukan banyak garis/border.

### 5.4 Komponen bersama
- Header publik dan footer.
- Tombol primer, sekunder, teks, dan destruktif.
- Input, textarea, select, checkbox, uploader, dan pesan validasi.
- Kartu berita, kartu UMKM, kartu potensi pertanian, kartu agenda.
- Breadcrumb, pagination, search/filter, badge status.
- Tabel responsif, empty state, loading state, error state, dan konfirmasi tindakan.
- Komponen dialog/modal untuk konfirmasi tindakan penting pada admin.

## 6. Spesifikasi Halaman Publik

### 6.1 Beranda
Urutan konten yang disarankan:
1. **Header**: logo/identitas resmi, nama Desa Marga Agung, menu utama.
2. **Hero**: foto desa yang telah disetujui, judul singkat, deskripsi, dan CTA “Jelajahi Profil Desa” serta “Lihat APBDes”.
3. **Ringkasan desa**: jumlah penduduk dan informasi utama lain yang datanya telah diverifikasi.
4. **Akses cepat**: APBDes, Profil Desa, Potensi Desa, Berita, dan Ruang Aspirasi.
5. **Potensi unggulan**: sorotan pertanian dan UMKM.
6. **Berita terbaru**: kartu berisi gambar, kategori, tanggal, judul, ringkasan, dan tautan detail.
7. **Agenda mendatang**: nama agenda, tanggal/waktu jika tersedia, lokasi bila dikonfirmasi.
8. **Footer**: kontak, alamat, tautan peta, dan tautan penting resmi.

**Ketentuan:** Jangan mengarang jumlah penduduk, berita, statistik, alamat, atau konten desa. Jika data belum tersedia, gunakan placeholder yang jelas hanya di lingkungan pengembangan dan jangan publikasikan sebagai fakta.

### 6.2 Profil Desa
- Navigasi bagian/anchor: Sejarah, Visi dan Misi, Struktur Pemerintahan, Lokasi.
- Sejarah ditampilkan dalam format teks yang mudah dibaca, bukan blok teks terlalu lebar.
- Visi dan misi dipisahkan secara visual.
- Struktur pemerintahan menggunakan kartu atau tabel yang responsif berisi foto, nama, jabatan.
- Informasi geografis dan peta hanya ditampilkan berdasarkan koordinat/alamat yang telah diverifikasi.
- Sediakan fallback berupa alamat teks jika peta tidak dapat dimuat.

### 6.3 Transparansi APBDes
- Judul halaman dan penjelasan periode/tahun data.
- Pemilih tahun/periode yang tersedia.
- Ringkasan pendapatan dan belanja yang berasal dari data resmi.
- Tabel sebagai representasi utama agar angka tetap bisa diakses; diagram boleh menjadi pendamping.
- Daftar dokumen PDF per tahun dengan nama dokumen, periode, tanggal unggah bila tersedia, dan tombol “Unduh PDF”.
- Tampilkan keadaan kosong jika dokumen untuk tahun tertentu belum tersedia.
- Angka ringkasan harus sesuai dokumen resmi; tampilkan satuan mata uang dan format angka secara konsisten.
- Jangan menyajikan diagram sebagai satu-satunya cara membaca informasi.

### 6.4 Potensi Desa
Sediakan tab atau filter **Pertanian** dan **UMKM**.

**Pertanian dan kelompok tani**
- Nama komoditas dan deskripsi.
- Informasi musim panen jika tersedia.
- Nama kelompok tani dan kontak yang disetujui untuk dipublikasikan.
- Foto yang relevan dan telah memperoleh persetujuan.

**UMKM**
- Kartu produk/usaha: foto, nama usaha, produk, deskripsi singkat.
- Tombol kontak WhatsApp hanya jika nomor telah disetujui untuk publikasi.
- Nomor ditampilkan dengan format yang mudah dibaca.
- Sediakan pencarian/filter jika jumlah item cukup banyak.

### 6.5 Berita dan Agenda
**Daftar berita**
- Kartu atau daftar dengan gambar, kategori, tanggal, judul, ringkasan, dan tautan detail.
- Pagination atau “Muat lainnya” bila diperlukan.
- State khusus saat belum ada berita yang dipublikasikan.

**Detail berita**
- Judul, kategori, tanggal, gambar utama, isi artikel, dan navigasi kembali/berita terkait jika tersedia.
- Gambar memiliki teks alternatif yang deskriptif.
- Konten draf tidak boleh tampil di situs publik.

**Agenda**
- Tampilkan agenda mendatang dalam daftar yang mudah dipindai.
- Tampilkan tanggal dan informasi lokasi hanya jika tersedia/terverifikasi.
- Agenda yang sudah lewat dapat dipisahkan atau tidak ditampilkan sesuai keputusan pemilik produk.

### 6.6 Ruang Aspirasi
- Pengantar singkat yang menjelaskan tujuan formulir dan cara pesan ditangani.
- Field **Nama** dan **Isi Pesan** wajib; **NIK opsional** dan hanya jika benar-benar dibutuhkan.
- Tampilkan catatan privasi ringkas, tujuan pengumpulan data, serta pihak yang dapat mengaksesnya.
- Validasi di sisi klien untuk kenyamanan dan wajib diulang di server.
- Tampilkan error dekat field terkait dengan bahasa yang jelas.
- Setelah berhasil, tampilkan konfirmasi pengiriman tanpa membocorkan informasi sensitif.
- Terapkan perlindungan spam/rate limit di backend; UI dapat menyertakan mekanisme anti-spam yang disepakati.
- Jangan menampilkan aspirasi warga secara publik tanpa proses persetujuan khusus.

### 6.7 Kontak dan Lokasi
- Tampilkan nomor telepon kantor, email resmi, alamat, dan tautan peta hanya setelah diverifikasi.
- Gunakan tombol yang sesuai untuk menelepon, mengirim email, atau membuka peta.
- Jangan membuat kontak atau koordinat contoh terlihat seperti data nyata.

## 7. Spesifikasi Dashboard Admin

### 7.1 Kerangka dashboard
- Layout desktop: sidebar, header halaman, area konten.
- Layout smartphone: navigasi yang dapat dibuka/tutup dan formulir satu kolom.
- Header menampilkan judul halaman, pengguna yang login, dan menu akun.
- Sediakan indikator lokasi halaman/breadcrumb.
- Tabel panjang perlu pencarian, filter, pagination, dan tampilan responsif.

### 7.2 Ringkasan dashboard
Tampilkan ringkasan yang membantu pekerjaan, bukan metrik dekoratif:
- Jumlah konten berita terpublikasi/draf.
- Jumlah data UMKM dan pertanian.
- Jumlah aspirasi yang belum diproses.
- Shortcut ke tindakan yang sering digunakan.
- Nilai hanya berasal dari database; jangan menampilkan angka statis yang menyesatkan.

### 7.3 Pola halaman pengelolaan
Gunakan pola konsisten:
1. Judul dan deskripsi halaman.
2. Tombol aksi utama, misalnya “Tambah Berita”.
3. Pencarian/filter bila relevan.
4. Tabel/daftar data.
5. Aksi per baris: Lihat, Edit, Hapus, atau Ubah Status sesuai izin.
6. Form tambah/edit dengan label jelas dan validasi.
7. Konfirmasi sebelum penghapusan atau aksi yang berdampak besar.

### 7.4 Modul admin
- **Profil Desa:** edit sejarah, visi-misi, lokasi, dan informasi profil.
- **Struktur Perangkat:** CRUD nama, jabatan, foto, dan urutan tampilan.
- **APBDes:** unggah PDF, pilih tahun/periode, input ringkasan pendapatan/belanja, dan kelola dokumen.
- **Pertanian/Kelompok Tani:** CRUD komoditas, musim panen bila tersedia, deskripsi, dan kontak yang disetujui.
- **UMKM:** CRUD nama usaha, produk, deskripsi, foto, dan WhatsApp yang disetujui.
- **Berita:** CRUD judul, isi, kategori, gambar, tanggal, status draf/publikasi.
- **Agenda:** CRUD judul, deskripsi, tanggal/waktu, lokasi, dan status yang diperlukan.
- **Aspirasi:** daftar pesan, waktu kirim, status “Baru”, “Sudah Dibaca”, “Selesai”, dan balasan jika digunakan.
- **Akun/Hak Akses:** tampilkan hanya bagi peran yang berwenang; aturan final ditetapkan bersama desa.

### 7.5 Status dan umpan balik
Setiap tindakan harus memiliki umpan balik:
- Berhasil: notifikasi singkat, misalnya “Perubahan berhasil disimpan.”
- Gagal: penjelasan yang membantu tanpa membeberkan detail teknis sensitif.
- Memuat: indikator loading dan cegah pengiriman ganda.
- Data kosong: jelaskan kondisi dan aksi berikutnya.
- Penghapusan: dialog konfirmasi dengan nama item yang jelas.
- Upload: tampilkan nama file, ukuran, progres jika tersedia, dan hasil validasi.

## 8. Responsivitas dan Aksesibilitas

Terapkan pendekatan **mobile-first**.

### Breakpoint rekomendasi
Breakpoint ini adalah usulan implementasi, bukan angka yang ditetapkan SKPL.

| Rentang | Perilaku desain |
|---|---|
| < 640 px | Satu kolom, navigasi mobile, tombol dan input lebar, tabel berubah menjadi kartu atau dapat digulir horizontal dengan petunjuk. |
| 640–767 px | Dua kolom untuk kartu yang sesuai; formulir tetap ringkas. |
| 768–1023 px | Layout tablet, navigasi menyesuaikan ruang. |
| ≥ 1024 px | Layout desktop penuh, grid multi-kolom, dashboard dengan sidebar. |

Checklist:
- Semua fungsi utama dapat digunakan dengan keyboard.
- Fokus keyboard terlihat jelas.
- Label form terhubung dengan field terkait.
- Pesan error tidak hanya menggunakan warna.
- Gambar memiliki alt text; gambar dekoratif menggunakan alt kosong.
- Tombol/link memiliki nama yang menjelaskan tujuannya.
- Kontras warna teks memadai.
- Target sentuh sekitar 44 × 44 px bila memungkinkan.
- Jangan menyampaikan informasi penting hanya melalui hover.
- Hormati preferensi reduced motion bila ada animasi.

## 9. Formulir, Validasi, dan Status

- Berikan label tetap; jangan mengandalkan placeholder sebagai satu-satunya label.
- Tandai field wajib dan opsional secara eksplisit.
- Pertahankan input yang valid ketika terjadi kesalahan agar pengguna tidak perlu mengisi ulang.
- Gunakan validasi inline dan ringkasan error bila formulir panjang.
- Format tanggal, angka, dan mata uang secara konsisten dalam Bahasa Indonesia.
- Validasi frontend hanya untuk pengalaman pengguna; validasi dan otorisasi server tetap wajib.
- Jangan menampilkan stack trace atau pesan database mentah kepada pengguna.

## 10. Aturan Gambar dan Dokumen

- Gunakan gambar asli yang layak dan disetujui untuk dipublikasikan.
- Optimalkan gambar; gunakan ukuran yang sesuai konteks dan format modern bila didukung.
- Hindari gambar besar sebagai latar jika menurunkan performa.
- Tampilkan placeholder visual yang konsisten saat gambar tidak tersedia.
- Unggahan harus membatasi tipe dan ukuran berkas, memeriksa konten file di server, dan menghasilkan nama file yang aman.
- Dokumen APBDes ditampilkan sebagai PDF yang bisa diunduh.
- Jangan percaya ekstensi file saja untuk validasi.
- Jangan mempublikasikan foto, nomor WhatsApp, atau informasi pribadi tanpa persetujuan yang sesuai.

## 11. Prinsip Kinerja

Target dari SKPL: halaman utama dimuat kurang dari 3 detik pada kondisi uji yang disepakati. Untuk mendukung target tersebut:
- Kompres gambar dan sajikan dimensi yang sesuai.
- Hindari mengunduh gambar yang belum terlihat di layar jika tidak diperlukan.
- Minimalkan JavaScript dan dependensi yang tidak dibutuhkan.
- Gunakan caching aset statis sesuai konfigurasi.
- Paginasikan daftar panjang.
- Hindari memuat seluruh data ke halaman jika hanya sebagian yang dibutuhkan.
- Uji pada perangkat dan jaringan yang mewakili pengguna, termasuk simulasi jaringan lambat.
- Catat kondisi dan hasil pengujian; jangan menjamin target tanpa pengukuran.

## 12. Implementasi Frontend dan Batasan Teknis

SKPL menetapkan:
- Backend: PHP 8.x.
- Framework backend: Laravel (versi final harus dipastikan masih didukung dan kompatibel saat pengembangan; SKPL menyebut Laravel 10 atau 11 sebagai opsi awal).
- Database: MySQL atau MariaDB.
- Frontend: HTML5, CSS, JavaScript, dengan **satu** pendekatan utama antara Tailwind CSS atau Bootstrap.
- Web server: Apache atau Nginx.
- Pengelolaan versi: Git.

**Keputusan yang perlu dikonfirmasi sebelum implementasi:**
1. Pilih Tailwind CSS atau Bootstrap; jangan mencampur keduanya tanpa alasan yang jelas.
2. Tetapkan versi Laravel yang masih mendapat dukungan keamanan pada saat proyek dimulai.
3. Tentukan apakah frontend dirender melalui Blade atau pendekatan lain yang kompatibel dengan arsitektur tim.
4. Tetapkan format dan batas ukuran gambar/PDF.
5. Tetapkan aturan role/permission Super Admin dan Staf.
6. Tetapkan siapa yang menyetujui publikasi setiap kategori data.

Dokumen ini adalah pedoman desain dan tidak menggantikan kebutuhan keamanan, validasi, otorisasi, atau aturan bisnis di backend.

## 13. Model Konten yang Perlu Didukung UI

Rincian berikut diturunkan dari kelompok data dalam SKPL. Ini merupakan panduan antarmuka; struktur database final harus dirancang dan ditinjau terpisah.

- **Profil Desa:** sejarah, visi, misi, informasi geografis/lokasi.
- **Perangkat Desa:** nama, jabatan, foto.
- **APBDes:** tahun/periode, ringkasan pendapatan, ringkasan belanja, PDF.
- **Potensi Pertanian:** komoditas, deskripsi, musim panen jika tersedia, kelompok tani, kontak yang disetujui.
- **UMKM:** nama usaha, produk, deskripsi, foto, WhatsApp yang disetujui.
- **Berita:** judul, isi, tanggal, gambar, kategori, status publikasi.
- **Agenda:** judul/deskripsi, tanggal/waktu, lokasi jika tersedia, status.
- **Aspirasi:** nama pengirim, pesan, waktu pengiriman, status penanganan, balasan jika ada; NIK tidak dikumpulkan kecuali diperlukan dan memiliki dasar serta perlindungan yang jelas.
- **Akun Admin:** identitas login, hash kata sandi, peran, dan informasi yang diperlukan untuk pengelolaan akses.

## 14. Kriteria Penerimaan Desain

Desain dapat diterima jika:
- [ ] Semua halaman publik yang tercantum di peta situs tersedia.
- [ ] Beranda memuat berita terbaru, jumlah penduduk, potensi unggulan, dan akses APBDes.
- [ ] APBDes menyediakan ringkasan yang dapat dibaca dan tautan unduh PDF per tahun yang tersedia.
- [ ] Profil memuat sejarah, visi-misi, struktur perangkat, dan lokasi berdasarkan data terverifikasi.
- [ ] Katalog pertanian dan UMKM menampilkan bidang data sesuai SKPL.
- [ ] Berita memiliki daftar dan detail; agenda mendatang dapat diakses.
- [ ] Form aspirasi memvalidasi masukan, menandai NIK opsional, dan menampilkan konfirmasi/error yang jelas.
- [ ] Halaman login dan seluruh halaman admin yang diperlukan telah dirancang.
- [ ] UI admin membedakan draf dan publikasi serta status penanganan aspirasi.
- [ ] Menu APBDes dan Berita dapat dijangkau maksimal dua langkah dari beranda.
- [ ] Layout telah diperiksa pada smartphone, tablet, dan desktop.
- [ ] Empty, loading, success, dan error states dirancang untuk alur utama.
- [ ] Konten contoh tidak disajikan sebagai data faktual desa.
- [ ] Kontras, label form, fokus keyboard, dan alt text diperhatikan.
- [ ] Rancangan mendukung target performa dan keamanan yang ditetapkan SKPL.

## 15. Instruksi untuk AI Coding Agent / Developer

Gunakan dokumen ini sebagai acuan desain utama ketika membangun UI website Desa Marga Agung.

1. Baca seluruh `DESIGN.md` dan SKPL sebelum mulai mengubah kode.
2. Periksa struktur repository dan teknologi yang sudah ada terlebih dahulu; jangan menghapus atau mengganti stack tanpa alasan.
3. Implementasikan halaman publik dan dashboard admin sesuai peta situs serta kebutuhan FR-01 sampai FR-12.
4. Terapkan design tokens dan komponen bersama agar seluruh halaman konsisten.
5. Bangun responsif dari layar kecil ke layar besar.
6. Gunakan data nyata hanya jika diberikan atau sudah diverifikasi. Untuk data yang belum tersedia, gunakan placeholder development yang jelas dan mudah diganti.
7. Jangan membuat kontrol visual palsu: setiap tombol, filter, form, pagination, dan aksi harus bekerja atau ditandai belum diimplementasikan.
8. Pastikan status draf tidak tampil di publik dan akses admin ditegakkan backend.
9. Jangan menyimpan NIK atau data pribadi yang tidak dibutuhkan; jangan mengekspos aspirasi warga ke publik.
10. Setelah implementasi, laporkan halaman yang selesai, kebutuhan yang belum terpenuhi, keputusan yang masih perlu konfirmasi, dan hasil pengujian responsivitas.

## 16. Hal yang Perlu Dikonfirmasi dengan Pemerintah Desa

- Logo, warna, foto hero, dan aset visual resmi.
- Data jumlah penduduk dan sumber/tanggal datanya.
- Konten sejarah, visi-misi, struktur organisasi, dan lokasi.
- Dokumen APBDes serta angka ringkasan yang sudah diverifikasi.
- Data kelompok tani, UMKM, foto, dan persetujuan publikasi kontak.
- Konten berita dan agenda yang disetujui untuk publikasi.
- Kontak resmi kantor desa.
- Kebutuhan nyata pengumpulan NIK pada aspirasi, kebijakan retensi, dan pihak yang berhak mengakses.
- Hak akses rinci Super Admin dan Staf.
- Kebijakan backup, hosting, batas unggahan, dan penanggung jawab pemeliharaan.

---

**Dokumen:** `DESIGN.md`  
**Produk:** Website Profil dan Informasi Desa Marga Agung  
**Acuan utama:** SKPL *Website Profil dan Informasi Desa Marga Agung*  
**Status:** Rekomendasi desain awal untuk ditinjau dan disetujui sebelum implementasi.
