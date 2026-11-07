# ujiManual.md — Daftar uji manual (setelah semua fase selesai)

> Hal yang **belum bisa diuji otomatis** atau butuh lingkungan nyata (MySQL, SMTP, browser, server hosting).
> Claude menambah bagian baru di akhir setiap fase. Centang `[x]` setelah Anda mengujinya sendiri.

---

## Fase 0 — Persiapan

- [ ] Versi PHP di hosting sama dengan atau lebih tinggi dari **8.4.1** (`php -v` di server). Jika hosting hanya PHP 8.3, kabari agar `composer.json`/lock disesuaikan.
- [ ] `composer install --no-dev` bersih di server/folder baru (bukan hanya `--dry-run`).
- [ ] Ekstensi PHP tersedia: `pdo_mysql`, `mbstring`, `fileinfo`, `gd`, `openssl`.
- [ ] `.env` produksi memakai `DB_CONNECTION=mysql` dan database `siprodes` (atau nama lain) terhubung.
- [ ] Nilai di `config/desa.php` (batas unggahan, NIK aspirasi, masa simpan aspirasi) sudah disetujui desa; ubah lewat `.env` bila berbeda.

## Fase 1 — Skema database

- [ ] `php artisan migrate:fresh --seed` berhasil di **MySQL/MariaDB** (hanya SQLite yang sudah diuji).
- [ ] `php artisan migrate:rollback` lalu `migrate` ulang berhasil di MySQL.
- [ ] Set `ADMIN_EMAIL` di `.env`, jalankan `php artisan db:seed --class=AdminAwalSeeder`: kata sandi acak tercetak sekali di konsol dan Anda bisa login memakainya.
- [ ] Di produksi `php artisan db:seed` **tidak** memuat data contoh (hanya data master + admin awal).
- [ ] Semua data contoh (kolom `contoh = true`) diganti/dihapus dengan data resmi sebelum rilis.
- [ ] Daftar Dusun 1–6 dan jabatan sesuai data resmi desa.

## Fase 2 — Autentikasi admin

- [ ] Buka `/admin` tanpa login: diarahkan ke halaman masuk.
- [ ] Login dengan email, lalu dengan username; "Ingat saya" tetap masuk setelah browser ditutup dan dibuka lagi.
- [ ] Salah kata sandi 5 kali: login terkunci dengan pesan "Terlalu banyak percobaan…"; setelah ±15 menit bisa mencoba lagi.
- [ ] Akun nonaktif tidak bisa masuk; akun yang dinonaktifkan saat sedang login langsung keluar di klik berikutnya.
- [ ] Tombol **Keluar** (sidebar dan menu akun di topbar, desktop dan HP) mengakhiri sesi; tombol Back browser tidak membuka halaman admin.
- [ ] **Email atur ulang kata sandi sungguhan**: isi SMTP di `.env` (`MAIL_MAILER=smtp`, dst.), kirim dari "Lupa kata sandi", email sampai (cek folder spam), isi dan tautannya benar (domain/HTTPS sesuai `APP_URL`), tautan berlaku 60 menit, hanya sekali pakai.
- [ ] Email tidak terdaftar dan email nonaktif menampilkan pesan yang sama seperti email terdaftar (dan tidak ada email terkirim).
- [ ] Halaman "Buat kata sandi baru" tampil rapi di HP 390 px; pesan galat (kurang dari 10 karakter, sama dengan email, konfirmasi beda) muncul.
- [ ] Kolom `terakhir_masuk_at` terisi di database setelah login.

## Fase 3 — Otorisasi peran & modul Akun

- [ ] Masuk sebagai **Super Admin**: semua menu sidebar tampil, termasuk "Akun & Hak Akses".
- [ ] Buat akun Staf lewat **Tambah Akun** dengan email sungguhan dan 1–2 modul; email "Buat kata sandi baru" sampai, tautan berfungsi, lalu Staf bisa login.
- [ ] Masuk sebagai Staf tadi: hanya menu modulnya yang tampil; membuka URL modul lain langsung (mis. `/admin/apbdes`, `/admin/akun`) menampilkan halaman 403.
- [ ] Pintasan dan daftar "Aspirasi Terbaru" di Dashboard Staf hanya muncul bila Staf berhak.
- [ ] Ubah modul Staf dari akun Super Admin; Staf melihat perubahan setelah memuat ulang halaman (tanpa perlu login ulang).
- [ ] Nonaktifkan Staf saat Staf sedang login di browser lain: klik berikutnya mengeluarkannya.
- [ ] Coba menonaktifkan akun sendiri dan menurunkan/menonaktifkan Super Admin terakhir: ditolak dengan pesan galat yang jelas.
- [ ] Tombol "Kirim tautan atur ulang kata sandi" (ikon gembok): email terkirim; klik dua kali berturut-turut menampilkan pesan "coba lagi dalam satu menit".
- [ ] Modal Tambah/Ubah Akun tampil baik di HP 390 px (daftar modul tidak terpotong, tombol ≥ 44 px) dan galat validasi (email ganda, username salah) tampil di dalam modal.
- [ ] **Konfirmasi ke desa**: matriks izin (Staf hanya modul yang ditugaskan; Akun khusus Super Admin) sudah sesuai kebijakan.

## Fase 4 — Halaman publik membaca database

- [ ] Jalankan `php artisan migrate:fresh --seed` lalu buka semua halaman publik di browser (desktop dan HP 390 px): Beranda, Profil, APBDes, Potensi, Berita, Detail Berita, Kontak, Aspirasi. Tidak ada tampilan rusak.
- [ ] Data masih berlabel contoh/“Menunggu data resmi”: tidak ada yang tampak sebagai data resmi sebelum diganti.
- [ ] Hapus semua baris `berita`/`agenda`/`umkm` di database (atau pakai database kosong): Beranda, Berita, Potensi, APBDes menampilkan keadaan kosong yang rapi (tanpa galat).
- [ ] Ubah satu berita ke status `draf` langsung di database: hilang dari Beranda, daftar, pencarian, dan detailnya 404 — tanpa menunggu cache.
- [ ] Isi `profil_desa.alamat/telepon/email/jam_layanan/lintang/bujur` tanpa mencentang `kontak_terverifikasi`: tidak tampil di footer, Profil, Kontak. Centang: tampil, tautan telepon/email/peta berfungsi.
- [ ] Pencarian Potensi: ketik kata kunci (mis. nama usaha di halaman 2 UMKM) → ditemukan; pindah tab Pertanian ⇄ UMKM tetap bekerja, dan setelah mencari, tab terakhir tetap terbuka; tautan paginasi UMKM mempertahankan kata kunci.
- [ ] Pencarian berita dengan tanda kutip, `%`, atau `_` tidak menimbulkan galat.
- [ ] Matikan MySQL sebentar lalu buka `/transparansi-apbdes`: tampil “Data APBDes gagal dimuat” (bukan halaman galat teknis); halaman lain menampilkan halaman 500 desa tanpa detail teknis (`APP_DEBUG=false`).
- [ ] Ukur waktu muat Beranda/Berita di MySQL dengan data lebih banyak (≥ 100 berita): masih cepat (< 3 dtk, NFR-01).
- [ ] Nilai teks bawaan di Beranda/Profil/Potensi (hero, pengantar, ajakan) sudah disetujui desa — teks ini masih tertulis di `KontenPublik`, belum bisa diubah dari admin.

## Fase 5 — Admin A: Berita, Agenda, Ringkasan

- [ ] Jalankan `php artisan storage:link` (Fase 8 mendokumentasikannya) agar gambar berita tampil di browser; tanpa itu gambar hanya tersimpan.
- [ ] Tambah berita dengan editor (tebal, miring, subjudul, daftar, tautan, kutipan) lalu **Simpan & Terbitkan**: berita muncul di Beranda, daftar, dan detailnya di situs publik; tampilannya (spasi, subjudul, kutipan, daftar, tautan) rapi di desktop dan HP.
- [ ] Simpan sebagai Draf lalu ubah menjadi Terbit, dan sebaliknya: tampil/hilang di situs publik segera.
- [ ] Unggah gambar utama JPG/PNG/WebP asli (< 2 MB): tampil di detail berita dengan alt yang benar. Coba file > 2 MB, file PDF yang diganti ekstensi menjadi `.jpg`, dan gambar sangat lebar: ditolak dengan pesan galat Bahasa Indonesia.
- [ ] Ganti lalu hapus gambar; periksa folder `storage/app/public/berita` — berkas lama ikut terhapus.
- [ ] Coba menempel `<script>`, HTML, dan tautan `javascript:` ke editor lalu terbitkan: tidak berjalan dan tidak tampil sebagai HTML di situs publik.
- [ ] Judul berita yang sama dua kali menghasilkan dua tautan berbeda; tautan lama tetap berfungsi setelah judul diubah.
- [ ] Agenda: tambah dengan/ tanpa waktu dan lokasi, ubah, hapus, dan filter mendatang/lewat; di situs publik hanya agenda terbit yang mendatang tampil.
- [ ] Ringkasan: angka berita terbit/draf, UMKM, pertanian, dan aspirasi baru cocok dengan isi database; masuk sebagai Staf yang hanya memegang modul Berita: kartu dan daftar Aspirasi serta UMKM tidak tampil.
- [ ] Tampilan HP 390 px untuk daftar berita, form berita (editor, uploader), tabel agenda, dan Ringkasan tidak terpotong.
- [ ] Catatan: perubahan status di halaman Aspirasi admin belum mengubah angka Ringkasan sampai Fase 9.

## Fase 6 — Admin B: Profil, Struktur, Potensi

- [ ] Profil → Sejarah: tempel naskah resmi (baris kosong = pisah paragraf), simpan; tampil di `/profil-desa` (paragraf pertama menjadi lead).
- [ ] Profil → Visi & Misi: isi visi + beberapa misi, ubah urutan/hapus/tambah butir; setelah disimpan badge “Menunggu naskah resmi desa” hilang di publik.
- [ ] Profil → Lokasi: isi alamat, koordinat, telepon, email. “Simpan Draf” tidak menayangkan apa pun; “Simpan & Terbitkan” + centang verifikasi menampilkan alamat/telepon/email/peta di Kontak, Profil, dan footer. Tautan telepon, email, dan Google Maps berfungsi.
- [ ] Profil → Data Ringkas: ubah angka dan sumber; di publik tanda “±” hilang setelah dicentang “sudah diverifikasi”.
- [ ] Struktur: tambah perangkat dengan foto persegi asli (JPG/PNG/WebP); foto non-persegi dan > 2 MB ditolak; ubah urutan dengan tombol panah (cek juga dengan keyboard); sembunyikan satu perangkat → hilang di publik; hapus → foto ikut terhapus di `storage/app/public/perangkat`.
- [ ] Potensi Pertanian: tambah komoditas dengan foto + alt, tandai unggulan; tampil di Potensi dan Beranda hanya bila Terbit; draf tidak tampil.
- [ ] Kelompok Tani: tambah dengan komoditas/dusun dari daftar; centang “disetujui” tanpa nomor ditolak; kontak tampil di publik hanya bila disetujui. **Konfirmasi ke desa**: bukti persetujuan kontak sudah ada sebelum dicentang.
- [ ] UMKM: tambah usaha dengan WhatsApp `0812…` → tautan `wa.me/62812…` benar di publik; tanpa centang persetujuan nomor dan foto tidak tampil; foto asli (bukan foto AI/stok) dengan izin pemilik.
- [ ] Hapus komoditas yang dipakai kelompok tani: kelompok tetap ada (komoditasnya kosong “—”).
- [ ] Semua form di atas rapi di HP 390 px (uploader, daftar misi, tombol panah ≥ 44 px) dan galat validasi terbaca.
- [ ] Daftar Dusun 1–6 dan jabatan di data master sudah sesuai data resmi (belum ada halaman kelola master; saat ini lewat seeder/database).

## Fase 7 — APBDes

- [ ] Tambah periode baru: uraian bawaan muncul; isi angka dari dokumen APBDes resmi (format `12.500.000`), tambah/hapus baris; jumlah per bagian berubah langsung saat mengetik dan **sama** dengan angka di halaman publik setelah disimpan.
- [ ] “Simpan Draf”: tidak tampil di publik; “Simpan & Terbitkan”: tampil di `/transparansi-apbdes` (tabel, kartu ringkasan, diagram, persentase). Periksa pembulatan persentase terhadap dokumen resmi.
- [ ] Unggah PDF asli: judul wajib; ukuran tampil; di publik tombol “Unduh PDF” mengunduh berkas dengan nama sesuai judul. Coba PDF besar (> 10 MB), berkas `.exe`/`.docx` yang diganti nama menjadi `.pdf`, dan gambar: ditolak dengan pesan jelas.
- [ ] Periksa folder `storage/app/private/apbdes`: berkas bernama acak dan **tidak** dapat dibuka lewat URL langsung (`/storage/apbdes/...`).
- [ ] Periode berstatus draf: tautan unduhan dokumennya 404 untuk pengunjung umum, tetapi admin masih bisa mengunduh dari halaman admin.
- [ ] Hapus dokumen dan hapus periode: berkas PDF ikut hilang dari folder privat.
- [ ] Pembiayaan: pastikan cara pengisian “pembiayaan netto” (satu angka) cukup untuk dokumen APBDes desa; bila perlu rincian penerimaan/pengeluaran, kabari agar skemanya diperluas.
- [ ] Di server produksi tes unduhan PDF besar (± 10 MB) lewat HTTPS dan di HP; pastikan batas `upload_max_filesize` dan `post_max_size` PHP ≥ 12 MB.
- [ ] Tampilan HP 390 px: editor rincian (kolom anggaran, tombol hapus ≥ 44 px), daftar dokumen, dan halaman publik APBDes tidak terpotong.

## Fase 8 — Unggahan berkas & gambar

- [ ] Jalankan `php artisan storage:link` di server (atau `composer setup` di lingkungan baru); gambar yang diunggah dapat dibuka lewat `/storage/...` dan folder `public/storage` menunjuk ke `storage/app/public`.
- [ ] Unggah foto asli dari HP (JPG besar ± 3–5 MB dengan EXIF/lokasi) — bila lebih dari 2 MB ditolak dengan pesan “Ditolak: ukuran berkas melebihi 2 MB”; kecilkan lalu unggah ulang: tersimpan, posisi (potret/lanskap) benar, dan ukurannya ≤ 1920 px.
- [ ] Unduh gambar hasil unggahan dari situs publik dan periksa propertinya: tidak ada data lokasi (GPS) dan kamera di metadata.
- [ ] Coba unggah: berkas `.php`/`.exe`/`.svg`, PDF yang diganti nama menjadi `.jpg`, gambar rusak, dan PNG yang diganti `.jpg` — yang berbahaya ditolak, yang sah tersimpan dengan ekstensi sesuai isi.
- [ ] Pesan galat dan batas MB di uploader (sebelum kirim) sama dengan pesan dari server.
- [ ] Di folder `storage/app/public/*` semua nama berkas acak (tanpa nama asli). Coba menaruh `tes.php` di folder itu dan akses lewat `/storage/.../tes.php`: tidak dijalankan (Apache memakai `.htaccess`; **Nginx perlu aturan tambahan** — lihat panduan rilis Fase 11).
- [ ] Ganti foto, lalu hapus data: berkas lama hilang dari folder penyimpanan (periksa `berita/`, `perangkat/`, `komoditas/`, `umkm/`).
- [ ] Atur `upload_max_filesize` ≥ 12M, `post_max_size` ≥ 14M, `memory_limit` ≥ 256M di server; uji unggah gambar 4000 × 3000 px (harus sukses atau ditolak dengan pesan wajar, bukan halaman galat).
- [ ] Setelah semua modul dicoba, buka folder penyimpanan dan pastikan tidak ada berkas yatim yang menumpuk.

## Fase 9 — Ruang Aspirasi

- [ ] Kirim aspirasi dari HP sungguhan (dengan dan tanpa NIK): halaman “Aspirasi berhasil dikirim” muncul; di admin masuk sebagai Baru, badge sidebar bertambah, NIK hanya tampil samar (•••• •••• •••• 1234).
- [ ] Cek database: kolom `nik` berisi teks terenkripsi (bukan 16 digit), tidak ada kolom IP; aspirasi tidak muncul di halaman publik mana pun.
- [ ] Kirim sangat cepat (otomatis lewat skrip/curl tanpa menunggu 3 detik) atau mengisi kolom tersembunyi: tidak tersimpan. Pastikan pengguna manusia yang mengisi pelan tidak pernah ditolak, termasuk pengguna dengan pengisi otomatis peramban.
- [ ] Kirim 6 kali dalam 10 menit dari satu jaringan: kiriman ke-6 ditolak (halaman 429 “Terlalu banyak permintaan”); pastikan halaman 429 terbaca baik (belum memakai tampilan khusus desa).
- [ ] Admin: filter Baru/Sudah Dibaca/Selesai, cari, buka detail; “Tandai Sudah Dibaca”, “Simpan & Tandai Selesai”, ubah kembali ke Baru; riwayat mencatat status, catatan, dan nama petugas. Daftar dengan > 20 aspirasi terpaginasi.
- [ ] Tampilan HP 390 px: daftar → detail → kembali; tombol aksi ≥ 44 px.
- [ ] Penjadwal: pasang cron `* * * * * php /path/artisan schedule:run` di server, lalu jalankan `php artisan aspirasi:anonimkan --hitung` dan `php artisan schedule:list`; uji anonimisasi di database percobaan dengan aspirasi selesai berusia > 24 bulan.
- [ ] **Konfirmasi ke desa**: masa simpan 24 bulan, NIK dikumpulkan atau tidak (`ASPIRASI_NIK`), dan kebijakan balasan (catatan internal saja, formulir tidak meminta kontak) sudah disetujui.
- [ ] Aspirasi yang sudah dianonimkan di admin tampil “Tanpa nama” dan “NIK: dianonimkan”; isi pesan tetap ada — periksa apakah desa menghendaki pesan ikut dihapus.

## Fase 10 — Audit log, pengamanan & data master

- [ ] Lakukan serangkaian tindakan sebagai admin (login, tambah/terbitkan/hapus berita, ubah peran akun, unggah dokumen APBDes, ubah status aspirasi) lalu buka **Akun & Hak Akses → Log Aktivitas**: semua tercatat dengan waktu WIB yang benar, nama petugas, dan IP.
- [ ] Salah kata sandi 5 kali: tercatat satu entri “Login dikunci”. Periksa tidak ada kata sandi/NIK/isi aspirasi di log atau tabel `log_aktivitas`.
- [ ] **Waktu**: bandingkan jam di log/riwayat aspirasi dengan jam sebenarnya (zona Asia/Jakarta; `APP_TIMEZONE`). Data lama yang disimpan sebelum perubahan zona waktu (bila ada) bisa bergeser 7 jam.
- [ ] Di server produksi dengan HTTPS: periksa header dengan `curl -I https://domain` dan alat seperti securityheaders.com / Mozilla Observatory — ada CSP, HSTS, X-Frame-Options, nosniff, Referrer-Policy.
- [ ] Jelajahi **semua** halaman publik & admin di Chrome, Firefox, dan Safari dengan konsol terbuka: tidak ada galat “Refused to …” dari Content-Security-Policy (menu HP, dialog, toast, tab Potensi, editor berita, uploader, formulir aspirasi, peta/tautan eksternal).
- [ ] `.env` produksi: `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://…`; cookie sesi (DevTools → Application) bertanda Secure, HttpOnly, SameSite=Lax; membuka `http://` dialihkan ke HTTPS oleh server. Bila di belakang proxy, isi `TRUSTED_PROXIES` dan cek IP di log benar (bukan IP proxy).
- [ ] Sengaja memicu galat 500 di produksi: tampil halaman galat desa tanpa jejak teknis.
- [ ] Data Master (Super Admin): ubah nama/urutan dusun dan jabatan sesuai data resmi; coba hapus jabatan yang dipakai perangkat (ditolak) dan dusun yang dipakai kelompok tani; staf biasa tidak melihat menu ini.
- [ ] Tampilan HP 390 px untuk halaman Akun (log) dan Data Master.
- [ ] **Konfirmasi ke desa**: daftar dusun dan jabatan resmi; kebijakan masa simpan log aktivitas (saat ini tidak dihapus otomatis).

## Fase 11 — Pengujian akhir & rilis

- [ ] Ikuti `docs/deploy.md` dari nol di server percobaan yang meniru produksi (PHP ≥ 8.4.1, MySQL, HTTPS): `migrate --force`, `MasterDataSeeder`, `AdminAwalSeeder`, `storage:link`, build, cache config/route/view.
- [ ] `php artisan migrate:fresh --seed` dan `php artisan test` di **MySQL** (set `DB_CONNECTION=mysql` pada database percobaan); perhatikan pencarian `LIKE ... ESCAPE`, kolom JSON, dan enkripsi NIK.
- [ ] Jalankan `composer update symfony/mime` (advisori CVE-2026-45070), lalu `php artisan test` dan uji email atur ulang kata sandi.
- [ ] Lighthouse (mode seluler) pada Beranda, Berita, Potensi, APBDes di server percobaan: target waktu muat < 3 detik (NFR-01); catat hasilnya di `docs/laporanBE.md`.
- [ ] Uji alur utama di browser dengan data dari database: lihat berita/APBDes/potensi, kirim aspirasi, login admin, terbitkan berita, unggah PDF APBDes, ubah status aspirasi, kelola akun. Ulangi di Chrome, Firefox, Safari (iOS) dan satu HP Android.
- [ ] Buka konsol browser di setiap halaman: tidak ada galat Content-Security-Policy atau font.
- [ ] Backup: jalankan skrip backup, lalu **pulihkan ke server percobaan** dan pastikan data, gambar, dan PDF utuh; simpan `APP_KEY` terpisah (NIK tak terbaca tanpa kunci ini).
- [ ] Matikan MySQL sebentar: halaman publik menampilkan halaman galat desa (bukan jejak teknis); nyalakan lagi dan situs pulih sendiri.
- [ ] Minta satu-dua perangkat desa mencoba memakai admin tanpa panduan; catat hal yang membingungkan.
- [ ] Semua keputusan desa di `docs/laporanBE.md` §5 sudah dikonfirmasi atau sengaja memakai nilai bawaan.

## Perapian tampilan — peta, menu samping admin, bilah editor

- [ ] Buka **Profil Desa** dan **Kontak & Lokasi** di Chrome, Firefox, dan Safari (iOS): atribusi "Leaflet | Citra © Esri…" terbaca jelas di atas citra satelit yang terang maupun gelap; tombol + dan − berukuran besar dan mudah ditekan dengan jempol.
- [ ] Di HP 390 px, geser dan perbesar peta dengan dua jari; pastikan kontrol zoom tidak menutupi penanda lokasi.
- [ ] Masuk admin di laptop dengan layar pendek (tinggi sekitar 700–800 px): bayangan tipis muncul di dasar menu samping bila masih ada item di bawah; gulir menu sampai "Data Master" dan "Akun & Hak Akses" tampil penuh; bayangan di dasar hilang saat sudah di ujung.
- [ ] Buka laci menu admin di HP: bayangan gulir tampil wajar dan tidak menimpa teks menu.
- [ ] Tambah/Ubah Berita: ketuk tombol B, I, H2, Daftar, Daftar bernomor, dan Tautan dengan jempol di HP; tidak ada yang meleset, dan bilah format membungkus rapi ke baris kedua tanpa menggeser halaman.

## Perapian tampilan — visi-misi, kartu kontak, halaman 403

- [ ] **Profil Desa → Visi dan misi** di database yang masih kosong: tampil keterangan "Naskah visi dan misi belum tersedia" dan lencana "Menunggu naskah resmi desa", tanpa "Butir misi 1–4". Isi hanya visi (admin → Profil → Visi & Misi) lalu muat ulang: visi tampil dan misi berketerangan "Naskah misi belum tersedia"; isi keduanya dan centang naskah resmi: lencana hilang.
- [ ] **Kontak & Lokasi** sebelum data diverifikasi: dua kartu saja ("Telepon dan email resmi" dan "Formulir daring"). Setelah admin memverifikasi kontak dan mengisi telepon serta email: kartu telepon dan email tampil terpisah dengan tombol "Telepon" dan "Kirim email" yang bekerja di HP. Isi telepon saja: tidak ada kartu email kosong.
- [ ] Buat satu akun **Staf** dengan satu modul (mis. Berita), masuk, lalu ketik langsung `/admin/apbdes` di bilah alamat: tampil halaman "Akses tidak diizinkan" di dalam kerangka admin dengan menu samping hanya berisi modul milik Staf, dan tombol "Ke Ringkasan" berfungsi. Ulangi di HP 390 px (menu laci tetap bisa dibuka).
- [ ] Keluar dari admin lalu buka `/admin/apbdes`: diarahkan ke halaman masuk (bukan halaman 403).
- [ ] **Konfirmasi ke desa**: nomor telepon, email resmi, jam layanan kantor, serta naskah visi dan misi yang sah (data ini belum ada, tidak dikarang).

## Perapian tampilan — poles kecil, catatan berulang, ritme Beranda

- [ ] **Beranda** di Chrome, Firefox, dan Safari (iOS), layar lebar dan HP: pita hijau "Data singkat desa" terbaca jelas (putih di atas hijau), sumber angka tertulis sekali di bawah deret angka, dan "Layanan informasi" tampak terpisah di bawahnya. **Putuskan bersama desa apakah pita hijau ini diterima**, karena menyimpang dari desain Figma (kartu putih).
- [ ] Beranda di 1024–1280 px: tombol "Jelajahi profil desa" sejajar dengan paragraf; catat bila turun ke baris kedua dan apakah itu mengganggu.
- [ ] **Profil Desa** di HP 360–390 px: keempat tab (Sejarah, Visi dan Misi, Struktur Pemerintahan, Lokasi) terlihat tanpa menggeser; ketuk tiap tab dan pastikan halaman menggulir ke bagian yang benar serta tab aktif berganti saat menggulir.
- [ ] **Kontak & Lokasi**: tombol "Buka di Google Maps" berada di dasar kartu Kantor desa di desktop dan tidak terpotong di HP.
- [ ] **Berita**: kotak pilihan kategori dan kotak pencarian sejajar di dasar pada layar ≥ 1024 px; di HP tersusun bertumpuk tanpa celah janggal.
- [ ] **Potensi Desa → Kelompok tani**: selama belum ada kontak yang disetujui, tidak ada kolom Kontak dan hanya satu catatan di bawah tabel. Di admin, setujui satu kontak kelompok tani (centang "Kontak disetujui untuk dipublikasikan") lalu muat ulang halaman publik: kolom Kontak muncul, kelompok lain berketerangan "Belum disetujui untuk publikasi". Periksa juga tampilan HP (kartu bertumpuk).
- [ ] **Transparansi APBDes** pilih tahun tanpa dokumen: keadaan kosong arsip berukuran ringkas dan tidak menimpa konten di bawahnya.
- [ ] **Ruang Aspirasi** di HP: kotak "Isi Pesan" tidak punya pegangan ubah ukuran; di laptop pegangan masih ada dan hanya bisa menarik ke bawah.
- [ ] Jika Anda menjalankan `npm run dev`, hentikan dengan **Ctrl+C** (bukan dimatikan paksa) agar berkas `public/hot` dihapus Vite; bila berkas itu tertinggal, halaman tampil tanpa gaya.

## Perapian tampilan — halaman Profil Desa

- [ ] **Profil Desa → Sejarah**: kotak "Fakta singkat" menutup dengan "Sumber: …"; bila admin menulis sumber yang sudah diawali kata "Sumber", kata itu tidak muncul dua kali.
- [ ] **Profil Desa → Lokasi** dan **Kontak & Lokasi**: keterangan di bawah peta berbunyi "Gunakan tombol + dan − untuk memperbesar peta."; di laptop, klik peta lalu gulir dengan roda mouse tetap bisa memperbesar, dan di HP cubit dua jari bekerja.
- [ ] **Konfirmasi ke desa**: foto dan nama resmi perangkat desa (masih data contoh).

## Perapian tampilan — halaman Kontak & Lokasi

- [ ] Di HP, ketuk tombol "Buka di Google Maps" dan tautan "Lihat peta lebih besar": keduanya membuka tab baru dengan lokasi yang benar (Google Maps dan OpenStreetMap).
- [ ] **Konfirmasi ke desa**: telepon, email, dan jam layanan kantor. Selama belum diisi dan diverifikasi, halaman menampilkan satu kartu keterangan (bukan kartu kosong).

## Perapian tampilan — halaman Ruang Aspirasi

- [ ] Di HP, buka **Ruang Aspirasi**: catatan privasi ringkas di atas formulir berwarna hijau dengan ikon perisai; peringatan kuning di dalam formulir tetap ada; kotak "Privasi Anda" lengkap muncul di bawah formulir.
- [ ] Kirim satu aspirasi sungguhan dari HP: kartu "Aspirasi berhasil dikirim" tampil dan dibaca pembaca layar; pesan itu muncul di admin dengan status Baru. Kirim formulir kosong: ringkasan galat dan pesan per kolom tampil.
- [ ] Kirim lebih dari 5 aspirasi dalam 10 menit dari satu jaringan: tampil halaman "Terlalu banyak permintaan" dengan keterangan yang ramah.
- [ ] **Konfirmasi ke desa**: apakah NIK dikumpulkan (`ASPIRASI_NIK`), masa simpan 24 bulan, dan kebijakan balasan.

## Perapian tampilan — halaman Transparansi APBDes

- [ ] Buka **Transparansi APBDes** di laptop: tabel Pendapatan dan Belanja berdampingan; tabel yang barisnya lebih sedikit lebih pendek dan tidak menyisakan ruang kosong di dalam kartu. Di HP, tabel menjadi kartu bertumpuk dengan tombol "Lihat rincian lengkap".
- [ ] Ganti tahun lewat pemilih (2026, 2025, 2024): angka, tabel, dan diagram berganti; unduh satu dokumen PDF dan pastikan berkas terbuka.
- [ ] Hapus sementara semua periode APBDes di database percobaan (jangan di produksi): halaman menampilkan "Data APBDes belum dipublikasikan" tanpa jejak galat.

## Perapian tampilan — halaman Potensi Desa

- [ ] Buka **Potensi Desa → UMKM** di HP: kartu bertumpuk, tombol WhatsApp hanya pada usaha yang kontaknya disetujui dan membuka WhatsApp dengan nomor yang benar.
- [ ] Cari kata yang tidak ada (mis. "zzzxyz") di tab UMKM dan Pertanian: tampil "Tidak ada ... yang cocok dengan pencarian." tanpa baris "Menampilkan 0–0 dari 0 UMKM"; tautan "Hapus pencarian" mengembalikan daftar penuh pada tab yang sama.
- [ ] Pindah tab dengan keyboard (panah kiri/kanan pada tab) dan dengan sentuhan; tab yang dipilih tetap aktif setelah pencarian dikirim.
- [ ] **Konfirmasi ke desa**: foto resmi komoditas dan produk UMKM (izin pemilik), serta kontak kelompok tani yang boleh dipublikasikan.

## Perapian tampilan — halaman Berita & Agenda

- [ ] Di laptop, buka **Berita & Agenda** dan gulir daftar berita: kolom "Agenda desa" ikut menempel di atas layar dan tidak menimpa teks lain; di layar pendek (tinggi sekitar 700 px) kolom agenda bisa digulir sendiri sampai "Agenda yang telah lewat" terjangkau.
- [ ] Buka "Agenda yang telah lewat" dengan keyboard (Tab, lalu Enter atau Spasi): bagian itu terbuka dan fokusnya terlihat jelas, tidak terpotong di tepi kolom.
- [ ] Di HP dan tablet, kolom agenda tetap di bawah daftar berita dan tidak menempel.
- [ ] Filter kategori dan pencarian: pilih kategori, cari kata, lalu hapus filter; daftar dan paginasi menyesuaikan.
- [ ] **Konfirmasi ke desa**: foto sampul berita asli (kini penampung).

## Perapian tampilan — admin: dialog, laci menu, tautan atas

- [ ] Di laptop dengan jendela peramban pendek (tinggi sekitar 600–650 px) dan di HP, buka **Akun & Hak Akses → Tambah Akun**: gulir dialog sampai tombol "Buat Akun" terlihat dan bisa ditekan; klik di area gelap di luar dialog menutupnya; tombol Esc juga menutup.
- [ ] Ulangi di **Berita → ikon hapus** (dialog "Hapus berita ini?") dan di Agenda: dialog tampil di tengah, fokus berada di dalamnya, Batal menutupnya tanpa menghapus.
- [ ] Di HP, buka laci menu admin lewat tombol menu, tekan Tab berulang: fokus tetap di dalam laci; Esc menutupnya dan fokus kembali ke tombol menu.
- [ ] Di HP, tombol "Lihat situs publik" (ikon mata) di bilah atas mudah ditekan dan membuka situs publik di tab baru.

## Perapian tampilan — tipografi, ringkasan Staf, halaman 500

- [ ] Di HP, judul besar (Beranda, Profil, APBDes) terpatah seimbang tanpa satu kata menggantung sendirian; angka pada kartu statistik dan tabel APBDes rata dan sejajar.
- [ ] Masuk sebagai akun **Staf** dengan satu-dua modul: di bawah sapaan Ringkasan tertulis "Modul Anda: …" dan menu samping cocok dengan daftar itu. Buat Staf tanpa modul: tertulis "Belum ada modul yang ditugaskan kepada Anda. Hubungi Super Admin desa." Masuk sebagai Super Admin: baris itu tidak ada.
- [ ] Di server percobaan (bukan produksi), picu galat 500 sengaja: halaman "Terjadi gangguan pada situs" menampilkan tombol "Kembali ke Beranda" dan "Hubungi Desa", tanpa jejak teknis.
