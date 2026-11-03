# laporanBE.md — Laporan Backend Website Desa Marga Agung

Ringkasan hasil `docs/planBE.md` (fase 0–11). Rinciannya ada di Log `docs/planBE.md`; daftar uji manual yang harus dilakukan manusia ada di `docs/ujiManual.md`; panduan rilis di `docs/deploy.md`.

## 1. Stack dan versi

| Komponen | Versi / keterangan |
|---|---|
| PHP | ≥ 8.4.1 (`composer.json` `^8.4`; lokal 8.5.8) |
| Laravel | 13.38 |
| Symfony | 8.1.9 (di `composer.lock`) |
| Database | MySQL/MariaDB (produksi); SQLite memori untuk tes |
| Frontend | Blade + Tailwind 4 + Alpine.js, Vite; font di-host di repo |
| Paket tambahan | tidak ada (`league/commonmark` sudah bawaan Laravel) |

## 2. Status kebutuhan SKPL

| Kode | Status | Catatan |
|---|---|---|
| FR-01 Beranda | Selesai | Dari database; hanya konten `terbit` |
| FR-02 Profil Desa | Selesai | Sejarah, visi-misi, data ringkas, struktur perangkat, kontak/peta hanya setelah diverifikasi |
| FR-03 Transparansi APBDes | Selesai | Total dihitung dari rincian; PDF privat dengan unduhan yang memeriksa status terbit |
| FR-04 Potensi Desa | Selesai | Pencarian di server lintas halaman; kontak/WhatsApp/foto UMKM hanya bila disetujui |
| FR-05 Berita & Agenda | Selesai | Isi dirender aman (CommonMark, HTML dibuang) |
| FR-06 Ruang Aspirasi | Selesai | Simpan, NIK opsional terenkripsi, anti-spam (batas per IP, honeypot, token waktu) |
| FR-07 Autentikasi Admin | Selesai | Email/username, penguncian login, atur ulang kata sandi lewat email |
| FR-08 Manajemen Profil | Selesai | 4 tab, struktur perangkat dengan foto 1:1 dan urutan |
| FR-09 Manajemen APBDes | Selesai | Periode, rincian, dokumen PDF |
| FR-10 Manajemen Potensi & UMKM | Selesai | Komoditas (foto), kelompok tani, UMKM |
| FR-11 Manajemen Berita & Agenda | Selesai | CRUD, gambar utama, status draf/terbit |
| FR-12 Manajemen Aspirasi | Selesai | Status, catatan, riwayat dengan petugas; anonimisasi terjadwal |
| NFR-01 Kinerja < 3 dtk | Sebagian | Tidak ada N+1 (diuji pada 16 halaman); pengukuran Lighthouse di server nyata **belum** dilakukan |
| NFR-02 Keamanan | Terpenuhi pada uji otomatis | Lihat §4; belum ada uji penetrasi independen |
| NFR-03 Kontrol akses per peran | Selesai | Gate `akses-modul` + middleware di setiap rute; diuji untuk semua rute admin |
| NFR-04 Kegunaan/aksesibilitas | Sebagian | Hasil fase FE; belum diuji pengguna dan pembaca layar nyata |
| NFR-05 Ketersediaan | Di luar kode | SLA hosting; ada endpoint `/up` |
| NFR-06 Kompatibilitas browser | Belum | Baru diuji Chromium di fase FE; CSP perlu dicoba di Firefox/Safari |
| NFR-07 Pencadangan | Terdokumentasi | Skrip dan prosedur pemulihan di `docs/deploy.md`; **belum diuji di server** |
| TEC-02 Laravel | Laravel 13.38 | Konfirmasi versi final ke tim |
| TEC-03 MySQL | Terdokumentasi | Migrasi **belum dijalankan di MySQL** (hanya SQLite) |
| TEC-06 Unggahan | Selesai | Layanan terpusat, gambar ditulis ulang (tanpa EXIF/muatan tersembunyi) |

## 3. Hasil pengujian

`php artisan test`: **229 tes lolos** (±3.080 asersi), `npm run build` lolos. Tes baru per area:

| Area | Berkas tes |
|---|---|
| Skema, relasi, seeder | `Fase1SkemaTest` |
| Autentikasi | `Fase2AutentikasiTest` |
| Otorisasi, akun | `Fase3OtorisasiTest` |
| Halaman publik dari database | `Fase4PublikDatabaseTest` |
| Berita, agenda, ringkasan | `Fase5AdminKontenTest` |
| Profil, struktur, potensi | `Fase6AdminProfilPotensiTest` |
| APBDes | `Fase7ApbdesTest` |
| Unggahan | `Fase8UnggahanTest` |
| Aspirasi | `Fase9AspirasiTest` |
| Audit log, header, produksi, data master | `Fase10KeamananTest` |
| Keamanan akhir | `Fase11KeamananAkhirTest` |

## 4. Keamanan: apa yang diuji

- **Akses per peran:** setiap rute `/admin/*` dipanggil sebagai tamu (→ login), Staf tanpa penugasan (→ 403), Staf per modul (hanya modulnya), Staf dengan semua modul (modul Akun/Data Master tetap 403), Super Admin (tidak pernah 403/500), dan akun nonaktif. Izin dicek **sebelum** model di-bind sehingga tidak membocorkan keberadaan data lewat 404.
- **Brute force:** penguncian per login+IP, pesan sama untuk akun ada/tidak ada, batas permintaan tautan atur ulang.
- **XSS:** payload di semua input teks (berita, agenda, profil, perangkat, komoditas, kelompok, UMKM, APBDes, akun, data master, aspirasi) tidak muncul sebagai skrip/atribut event/tautan `javascript:` di halaman publik maupun admin (diperiksa lewat DOM).
- **SQL injection / karakter khusus:** semua parameter pencarian dan filter; LIKE di-escape dan selalu memakai binding.
- **CSRF:** formulir publik dan admin menolak permintaan tanpa token (419).
- **Sesi:** ID sesi diganti saat login, sesi dihapus saat logout.
- **Unggahan berbahaya:** SVG/PHP/`.phtml`, PDF palsu, polyglot JPEG, ukuran, dimensi, rasio; gambar ditulis ulang; PDF di disk privat.
- **Header:** CSP tanpa skrip inline, HSTS lewat HTTPS, X-Frame-Options, nosniff, dll. Produksi memaksa debug mati, HTTPS, cookie sesi aman.

## 5. Keputusan yang masih memakai nilai bawaan (perlu konfirmasi desa)

| Keputusan | Nilai bawaan |
|---|---|
| Versi PHP hosting | ≥ 8.4.1 (asumsi) |
| Matriks izin Staf | Staf hanya modul yang ditugaskan; Akun & Data Master hanya Super Admin |
| Batas unggahan | Gambar ≤ 2 MB, maks. 4000 px (dikecilkan ke 1920 px); PDF ≤ 10 MB |
| NIK pada aspirasi | Opsional, terenkripsi, tampil disamarkan (`ASPIRASI_NIK`) |
| Masa simpan aspirasi | 24 bulan setelah selesai, lalu nama & NIK dianonimkan (pesan dipertahankan) |
| Balasan aspirasi | Catatan internal saja |
| Dusun & jabatan | Dusun 1–6 dan 8 jabatan dari frontend (bisa diubah di Data Master) |
| Publikasi | Hanya akun dengan izin modul yang dapat menerbitkan; tercatat di audit log |
| Pembiayaan APBDes | Satu angka netto |
| Teks Beranda/Potensi (hero, pengantar, ajakan) | Tertulis di kode (`KontenPublik`), belum dapat diubah dari admin |

## 6. Risiko dan hal yang belum dilakukan

1. **MySQL belum diuji.** Semua tes dan migrasi berjalan di SQLite. Jalankan `migrate:fresh --seed` dan uji manual di MySQL sebelum rilis.
2. **Email sungguhan belum diuji** (SMTP): atur ulang kata sandi dan undangan akun bergantung padanya.
3. **Lighthouse/performa di server nyata** belum diukur; tidak ada pengujian beban.
4. **Browser lain** (Firefox, Safari, perangkat nyata) dan **pembaca layar** belum diuji; CSP perlu diperiksa di konsol.
5. **CSP memakai `'unsafe-eval'`** karena build standar Alpine; menghapusnya butuh migrasi ke build CSP Alpine (pekerjaan frontend besar).
6. **Audit log** belum dipaginasi/difilter dan tanpa masa simpan.
7. **Tidak ada pembersih berkas yatim** di penyimpanan, dan `.htaccess` hanya melindungi Apache (Nginx: lihat `docs/deploy.md`).
8. **Backup & pemulihan** baru berupa prosedur; harus diuji.
9. **Anonimisasi** hanya mengosongkan nama dan NIK; isi pesan bisa memuat data pribadi — tanyakan kebijakan desa.
10. **Halaman 429/419/503** masih tampilan bawaan Laravel (belum bertema desa).
11. Tidak ada uji penetrasi independen. `composer audit` melaporkan **1 advisori (medium): CVE-2026-45070, symfony/mime** (injeksi header email); jalankan `composer update symfony/mime` ke versi perbaikan dan uji ulang email sebelum rilis. `npm audit --omit=dev`: 0 kerentanan.

## 7. Temuan FE yang diselesaikan

#1 versi PHP (fase 0), #2 rute admin tanpa `auth` (2–3), #3 rincian APBDes (7), #4 unggahan tanpa batas (8), #5 email akun (3), #6 foto komoditas (6), #7 font dari internet (11: di-host di `resources/fonts`), #8 hash commit di `planFE.md` (0).
