# laporanFE.md — Laporan Frontend Website Desa Marga Agung

> Laporan akhir fase 11 (QA). Disusun sesuai DESIGN.md §15 poin 10: halaman yang selesai, kebutuhan yang belum terpenuhi, keputusan yang perlu dikonfirmasi desa, dan hasil uji responsivitas.
> Tanggal pengujian: 30 Oktober 2026. Seluruh data di aplikasi masih **data contoh** (`app/Support/DemoContent.php`, `DemoStore*.php`), bukan data resmi desa.

## 1. Ringkasan

- Fase 0–11 selesai. Stack: PHP 8.3/8.5, Laravel 13.38, Blade, Vite 8, Tailwind 4, Alpine.js (hanya untuk menu, tab, dialog, pengunggah, dan editor sederhana).
- Frontend lengkap untuk **8 halaman publik**, **keadaan status** (404, 403, 500, kosong, memuat, gagal), dan **seluruh modul admin** (Masuk, Lupa kata sandi, Ringkasan, Berita, Agenda, Profil, Struktur, APBDes, Potensi Pertanian, Kelompok Tani, UMKM, Aspirasi, Akun & Hak Akses).
- **Belum ada backend.** Tidak ada autentikasi, database, penyimpanan berkas, atau pengiriman email. Rute admin (`/admin/...`) **belum dilindungi middleware `auth`** dan dapat dibuka siapa saja. Ini wajib ditutup backend sebelum diluncurkan.
- Tes otomatis: **64 tes lolos** (`php artisan test`). `npm run build` tanpa galat.

## 2. Halaman yang selesai

### Publik (Figma: halaman `1:2` dan `1:3`)

| Halaman | Rute | Catatan |
|---|---|---|
| Beranda | `/` | Hero, data singkat (bertanda "perlu verifikasi"), layanan informasi, potensi unggulan, berita + agenda, ajakan aspirasi |
| Profil Desa | `/profil-desa` | Sejarah, visi-misi (placeholder bertanda), struktur, lokasi; kontak/alamat hanya tampil bila `verified` |
| Transparansi APBDes | `/transparansi-apbdes` | Pemilih tahun, ringkasan, tabel (kartu di HP), arsip PDF; angka masih `Rp —` |
| Potensi Desa | `/potensi-desa` | Tab Pertanian/UMKM, pencarian, paginasi; WhatsApp/kontak hanya bila disetujui |
| Berita & Agenda | `/berita`, `/berita/{slug}` | Filter, pencarian, paginasi; draf tidak tampil dan detailnya 404 |
| Ruang Aspirasi | `/ruang-aspirasi` | Validasi klien dan server, `@csrf`, `throttle:5,1`, belum menyimpan |
| Kontak & Lokasi | `/kontak-lokasi` | Semua kontak menunggu verifikasi |
| Status | 404, 403, 500, kosong, memuat, gagal | Keadaan contoh lewat `?demo=` hanya di `APP_ENV=local` |

### Admin (Figma: halaman `1:4`)

Masuk, Lupa kata sandi, Ringkasan, Berita (daftar, form, hapus), Agenda (daftar, form, hapus), Profil (4 tab), Struktur Perangkat, APBDes, Potensi Pertanian, Kelompok Tani, UMKM, Aspirasi (daftar + detail; tampilan HP berupa daftar kartu lalu detail), Akun & Hak Akses (khusus Super Admin; matriks izin berstatus draf).

Perbedaan yang disengaja dari Figma tercatat per fase di `docs/planFE.md` (bagian Log). Yang paling berpengaruh:
- Struktur Perangkat memakai tombol panah untuk mengurutkan (bukan seret baris) agar bisa dipakai dengan keyboard.
- Label "VISI" di Profil memakai `accent-soft` (bukan `accent`) agar kontrasnya ≥ 4,5:1.
- Data placeholder Figma yang tidak resmi (mis. "Komoditas 2/3", angka APBDes) tidak ditampilkan sebagai fakta.

### Jalan pintas khusus lokal (`APP_ENV=local`)

Hanya untuk meninjau tampilan; **harus hilang bersama `DemoStore` saat backend dipasang**.
- `?demo=kosong|memuat|gagal` pada Berita, Berita admin, dan APBDes.
- `?peran=super_admin|staf` mengganti peran contoh (menu Akun & Hak Akses).
- Formulir Masuk yang lengkap langsung membuka dashboard. Di lingkungan lain, Masuk selalu gagal dengan pesan umum.

## 3. Status kebutuhan SKPL

Keterangan: **FE selesai** = antarmuka dan validasi tampilan siap; **Menunggu backend** = butuh database, autentikasi, atau penyimpanan berkas.

| ID | Kebutuhan | Status frontend | Yang masih diperlukan |
|---|---|---|---|
| FR-01 | Beranda | FE selesai | Data dari database; tautan APBDes sudah berfungsi |
| FR-02 | Profil Desa | FE selesai | Naskah sejarah, visi-misi, nama dan foto perangkat resmi; peta sungguhan |
| FR-03 | Transparansi APBDes | FE selesai | Angka resmi, berkas PDF, rute unduhan sungguhan; tombol Unduh PDF masih nonaktif |
| FR-04 | Potensi Desa | FE selesai | Data kelompok tani/UMKM resmi, foto, persetujuan kontak |
| FR-05 | Berita dan Agenda | FE selesai | Data dari database, gambar utama |
| FR-06 | Ruang Aspirasi | FE selesai | **Penyimpanan aspirasi belum ada** (formulir hanya divalidasi); kebijakan NIK |
| FR-07 | Autentikasi Admin | FE selesai | **Autentikasi nyata, middleware `auth`/`guest`, penguncian setelah gagal berulang, email atur ulang** |
| FR-08 | Manajemen Profil | FE selesai | Penyimpanan ke database dan foto; tampil di publik setelah diterbitkan |
| FR-09 | Manajemen APBDes | FE selesai | Penyimpanan, pemeriksaan isi PDF di server, rute unduhan |
| FR-10 | Manajemen Potensi dan UMKM | FE selesai | CRUD ke database, foto produk; katalog publik membaca data admin |
| FR-11 | Manajemen Berita dan Agenda | FE selesai | CRUD ke database, gambar; publik hanya membaca status terbit |
| FR-12 | Manajemen Aspirasi | FE selesai | Penyimpanan, riwayat, dan catatan di database |
| NFR-01 | Kinerja < 3 detik | Terpenuhi pada kondisi uji (lihat §6.3) | Uji ulang di hosting sebenarnya |
| NFR-02 | Keamanan | Sebagian | Hashing kata sandi, perlindungan SQL injection, pembatasan login, pemeriksaan berkas: **backend**. Frontend: keluaran Blade di-escape (diuji), `@csrf`, `throttle` dasar |
| NFR-03 | Kontrol akses per peran | Tampilan saja | **Izin wajib ditegakkan di server.** Menu Akun disembunyikan untuk Staf dan halamannya 403, tetapi memakai peran contoh |
| NFR-04 | Kegunaan dan aksesibilitas | Terpenuhi pada uji otomatis | Uji pengguna dan pembaca layar nyata (lihat §6.2); ukuran teks dasar 16 px (≥ 14 px) |
| NFR-05 | Ketersediaan | Di luar lingkup frontend | SLA hosting dan pemantauan |
| NFR-06 | Kompatibilitas peramban | Baru diuji di Chromium (Edge) | Uji Firefox, Safari, dan perangkat nyata |
| NFR-07 | Pemeliharaan dan pencadangan | Di luar lingkup frontend | Prosedur backup |
| TEC-02 | Laravel | Memakai Laravel 13.38 | SKPL menyebut Laravel 10/11 dan meminta verifikasi versi yang masih didukung; konfirmasi versi final |

## 4. Daftar `TODO(backend)`

Dapat dicari ulang dengan `grep -rn "TODO(backend)" app resources routes`. Dikelompokkan:

1. **Autentikasi dan izin** — `routes/web.php` (grup `admin.`), `AdminAuthController` (masuk, keluar, lupa kata sandi), `Peran` (peran dari pengguna yang masuk), `AdminAkunController` (izin Super Admin di server, buat akun tanpa kata sandi + tautan, atur ulang kata sandi), `AppServiceProvider` (pengguna dan jumlah aspirasi baru), semua `Admin*Controller` (middleware `auth` + izin per peran).
2. **Penyimpanan data** — `DemoContent`, `DemoStore`, `DemoStoreKoleksi` diganti model dan database; `PublicPageController` membaca database (hanya status terbit); `AdminProfilController` (profil di sesi → database); dashboard (hitung UMKM dan data pertanian).
3. **Aspirasi** — `AspirasiController@store`: simpan nama, pesan, waktu, status "Baru"; NIK hanya bila memang dibutuhkan; pembatasan spam final; riwayat dan catatan tindak lanjut.
4. **Berkas** — foto struktur (rasio 1:1), gambar berita, foto UMKM, PDF APBDes: simpan, periksa isi dan jenis di server, tetapkan batas ukuran (`x-ui.uploader` dan validasi `AdminBeritaController`, `AdminApbdesController`, `AdminStrukturController`, `AdminPotensiController`); rute unduhan PDF (`pages/public/apbdes`).
5. **Konten dan tampilan** — peta sungguhan (`pages/admin/profil`), pencarian lintas halaman Potensi (`resources/js/app.js`), editor isi berita: bersihkan dan render penanda teks ringan di server (`x-admin.editor`), paginator dan filter dari controller (`x-ui.pagination`, `x-ui.filter-dropdown`).
6. **Data master** — daftar dusun resmi (saat ini "Dusun 1–6"), daftar jabatan, kebijakan matriks izin (`DemoContent::izin()`).

## 5. Keputusan yang perlu dikonfirmasi desa

Mengacu ke DESIGN.md §16, ditambah temuan implementasi:

- Logo, warna, foto hero, dan aset visual resmi (logo masih placeholder; tanpa foto AI/stok).
- Jumlah penduduk, KK, luas wilayah, jumlah dusun beserta sumber dan tanggalnya (saat ini "survei awal 2026 · perlu verifikasi").
- Naskah sejarah, visi-misi, struktur dan nama perangkat, alamat kantor, dan koordinat.
- Dokumen APBDes dan angka ringkasan yang sudah diverifikasi; siapa yang berwenang menyetujui publikasi.
- Data kelompok tani dan UMKM, foto, dan **bukti persetujuan** publikasi kontak/WhatsApp.
- Kontak resmi kantor desa (telepon, email, jam layanan); semuanya belum tampil sampai diverifikasi.
- Berita dan agenda yang disetujui untuk publikasi.
- **Pengumpulan NIK pada aspirasi**: perlu tidaknya, tujuan, retensi, pembatasan akses. Saat ini kolom NIK opsional dan hanya tampil disamarkan di admin.
- Hak akses rinci Super Admin dan Staf (matriks izin masih draf, termasuk arti "Sesuai penugasan" untuk Profil, Struktur, dan APBDes).
- Batas ukuran unggahan, kebijakan backup, hosting, dan penanggung jawab pemeliharaan.
- Siapa yang berwenang membalas aspirasi, dan apakah balasan dikirim ke warga (saat ini catatan hanya internal).

## 6. Hasil QA

Lingkungan uji: Windows 11, Microsoft Edge (Chromium) headless, server `php artisan serve` lokal dengan hasil `npm run build`. Skrip uji (CDP + axe-core 4 + Lighthouse) dijalankan dari luar repositori dan tidak ikut di-commit.

### 6.1 Responsivitas (DESIGN.md §15 poin 10)

27 halaman (9 publik, 18 admin termasuk setiap tab Profil dan detail Aspirasi) diuji pada **360, 390, 768, 1024, dan 1440 px** (emulasi lebar viewport). Ukuran yang diperiksa: `scrollWidth` dokumen dibandingkan lebar viewport.

| Lebar | Hasil |
|---|---|
| 360 px | Tanpa gulir horizontal |
| 390 px | Tanpa gulir horizontal |
| 768 px | Tanpa gulir horizontal |
| 1024 px | Tanpa gulir horizontal (setelah perbaikan; lihat di bawah) |
| 1440 px | Tanpa gulir horizontal |

Temuan dan perbaikan: pada 1024 px, tabel **Admin Agenda** meluap 4 px karena kolom tetap terlalu lebar di samping sidebar 264 px. Kolom dipersempit (150/150/100/130 px) dan uji ulang bersih. Tabel lebar lain berubah menjadi kartu di bawah 1024 px.

Interaksi yang diuji pada 390 px (klik, Esc, fokus): menu layar penuh publik, laci menu admin, dan dialog hapus berita **terbuka, fokus terkunci di dalam, Esc menutup**; fokus kembali ke tombol pemicu pada menu publik dan laci admin. Aspirasi di HP menampilkan daftar dahulu, lalu detail setelah dipilih.

### 6.2 Aksesibilitas

- **axe-core** (aturan WCAG 2.0/2.1 A dan AA + best-practice) dijalankan pada 27 halaman di 390 px dan 1440 px.
- Putaran pertama: 60 pelanggaran (4 jenis). Semuanya sudah diperbaiki:
  - `region` (konten di luar landmark) di semua halaman: strip wilayah publik kini `role="region"` dan topbar admin kini `<header>`.
  - `color-contrast` pada label "VISI" (aksen di atas hijau ≈ 4,1:1): memakai `accent-soft`.
  - `label-content-name-mismatch` pada tombol editor (H2, 1. Daftar): nama aksesibel kini memuat teks yang terlihat.
  - `page-has-heading-one` pada Aspirasi admin: ditambahkan `<h1>` yang hanya terbaca pembaca layar.
- Putaran ulang: tersisa 2 temuan (`landmark-one-main`, `page-has-heading-one` pada dua halaman admin) yang ternyata artefak pemuatan skrip; tes server memastikan keduanya memiliki tepat satu `<h1>` dan `<main>`. Perbaikan nama tombol "1. Daftar" dilakukan setelah putaran terakhir dan belum dijalankan ulang melalui axe.
- Target sentuh: pemeriksaan otomatis pada 390 px menandai tautan breadcrumb (29 px) → area sentuh diperluas ke ≥ 44 px. Tombol ukuran S bertinggi visual 36 px dengan area sentuh 44 px (sesuai Figma).
- Tes otomatis menjamin: setiap `<img>` punya `alt` dan `loading="lazy"`, setiap halaman uji punya tepat satu `<h1>`, halaman admin/auth berisi `noindex`.
- **Belum diuji:** pembaca layar sungguhan (NVDA/VoiceOver), uji pengguna warga/perangkat desa, kontras pada keadaan hover/fokus secara menyeluruh, dan zoom 200 %.

### 6.3 Performa (NFR-01)

Lighthouse 13 pada build produksi (bukan server Vite), Edge headless, server PHP lokal. Kondisi: profil bawaan Lighthouse **seluler** (simulasi Slow 4G: RTT 150 ms, 1,6 Mbps, CPU 4× lebih lambat) dan **desktop**. Server lokal tidak mengompres respons (gzip), sehingga ukuran transfer lebih besar daripada di hosting yang layak.

| Halaman | Profil | Skor performa | FCP | LCP | TBT | CLS | Transfer |
|---|---|---|---|---|---|---|---|
| Beranda | Seluler | 98 | 1,2 dtk | 1,2 dtk | 160 ms | 0 | 430 KiB |
| Beranda | Desktop | 100 | 0,3 dtk | 0,3 dtk | 0 ms | 0 | 430 KiB |
| Berita | Seluler | 99 | 1,6 dtk | 1,6 dtk | 90 ms | 0 | 418 KiB |
| Berita | Desktop | 100 | 0,4 dtk | 0,4 dtk | 0 ms | 0 | 418 KiB |
| APBDes | Seluler | 100 | 1,1 dtk | 1,1 dtk | 50 ms | 0 | 422 KiB |
| APBDes | Desktop | 100 | 0,4 dtk | 0,4 dtk | 10 ms | 0 | 422 KiB |
| Aspirasi | Seluler | 99 | 1,0 dtk | 1,0 dtk | 100 ms | 0 | 412 KiB |
| Aspirasi | Desktop | 100 | 0,4 dtk | 0,4 dtk | 0 ms | 0 | 412 KiB |

Skor Aksesibilitas, Praktik Terbaik, dan SEO = 100 pada semua halaman di atas. **Target NFR-01 (< 3 detik) terpenuhi pada kondisi ini**; hasil di hosting sebenarnya dapat berbeda dan perlu diuji ulang.

Catatan: berkas aset produksi total ≈ 355 KB (JS 84 KB, CSS 52 KB, sisanya font). Saran perbaikan kecil: plugin font Vite menghasilkan dua format (woff2 dan woff) dengan deskriptor yang sama sehingga peramban memakai versi **woff** (≈ 30 KB per berkas, sekitar 25 % lebih besar dari woff2); pertimbangkan hanya woff2. Aktifkan kompresi gzip/brotli dan cache aset di server produksi. Halaman admin belum diukur dengan Lighthouse.

### 6.4 Draf, aspirasi, dan keamanan tampilan

- Berita dan agenda berstatus draf **tidak tampil** di halaman publik; `/berita/{slug-draf}` mengembalikan 404 (tes otomatis).
- Isi aspirasi dan nama pengirim **tidak muncul** di satu pun halaman publik (tes otomatis pada 7 halaman publik).
- NIK tidak pernah tampil utuh: `Format::nikSamar()` hanya menampilkan 4 digit terakhir (diuji); seluruh contoh aspirasi tidak berisi NIK.
- Masukan berisi `<script>` ditampilkan sebagai teks (di-escape Blade) pada daftar berita admin (tes otomatis).
- Menu Akun & Hak Akses disembunyikan dari Staf dan halaman/aksi akun mengembalikan 403. **Ini hanya pagar tampilan**; izin nyata harus ditegakkan backend.

### 6.5 Yang belum diuji

- Peramban selain Chromium (Firefox, Safari) dan perangkat sungguhan; sentuhan nyata.
- Pembaca layar, navigasi keyboard menyeluruh untuk seluruh halaman (hanya menu, laci, dan dialog yang diuji otomatis).
- Tampilan dialog dan form admin pada 360 px secara visual (hanya diuji lewat pemeriksaan gulir dan interaksi).
- Pengujian keamanan nyata (SQL injection, unggahan berkas, brute force login): menunggu backend.

## 7. Rekomendasi langkah berikutnya

1. Pasang autentikasi dan middleware `auth` serta izin per peran, lalu hapus jalan pintas lokal (`?peran`, `?demo`, login lokal).
2. Ganti `DemoContent`/`DemoStore*` dengan model dan database; pastikan publik hanya membaca status terbit.
3. Implementasikan penyimpanan aspirasi sesuai kebijakan NIK yang dikonfirmasi desa.
4. Selesaikan unggahan berkas (batas ukuran, pemeriksaan isi, penyimpanan) dan rute unduhan PDF.
5. Dapatkan konfirmasi desa atas butir di §5, lalu ganti seluruh placeholder dengan data resmi.
6. Uji ulang performa, aksesibilitas, dan responsivitas di hosting sebenarnya dan pada perangkat nyata.
