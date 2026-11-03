# planBE.md — Rencana Backend Website Desa Marga Agung

> Rencana kerja bertahap untuk Claude Code, lanjutan dari `docs/planFE.md` (fase 0–11 selesai).
> Acuan: SKPL di `docs/`, `docs/DESIGN.md`, `docs/laporanFE.md` (§3 status SKPL, §4 daftar `TODO(backend)`, §5 keputusan desa).
> Kerjakan **satu fase per sesi**. Setiap fase diakhiri titik periksa (checkpoint) untuk ditinjau manusia sebelum lanjut.

---

## Cara pakai

Ketik perintah ini di Claude Code setiap kali ingin melanjutkan:

```text
Baca docs/planBE.md dan kerjakan fase berikutnya yang statusnya belum "Selesai".
Ikuti bagian "Aturan kerja". Setelah fase selesai: centang tugasnya, isi Log,
ubah status fase di tabel, commit, lalu berhenti dan laporkan hasilnya.
```

Jika hasil fase belum sesuai, tulis koreksinya lalu minta: `Perbaiki fase N sesuai catatan, lalu laporkan lagi.`

---

## Status fase

| Fase | Nama | Status |
|---|---|---|
| 0 | Persiapan: versi PHP, database, alat bantu | Selesai |
| 1 | Skema database, model, factory, seeder | Selesai |
| 2 | Autentikasi admin | Selesai |
| 3 | Otorisasi peran & modul Akun | Selesai |
| 4 | Halaman publik membaca database | Selesai |
| 5 | Admin A: Berita, Agenda, Ringkasan | Selesai |
| 6 | Admin B: Profil, Struktur, Potensi (komoditas, kelompok tani, UMKM) | Selesai |
| 7 | APBDes: periode, rincian, dokumen PDF | Selesai |
| 8 | Unggahan berkas & gambar (terpusat) | Selesai |
| 9 | Ruang Aspirasi: simpan, anti-spam, penanganan | Belum |
| 10 | Audit log, pengamanan & data master | Belum |
| 11 | Pengujian akhir, panduan rilis & laporan | Belum |

Ubah kolom Status menjadi `Sedang` saat mulai dan `Selesai` setelah checkpoint lolos.

---

## Aturan kerja (wajib di setiap fase)

1. **Baca dulu** bagian fase yang dikerjakan, `docs/laporanFE.md` §4–§5, dan kode yang akan diubah. Cari sisa pekerjaan dengan `grep -rn "TODO(backend)" app resources routes`.
2. **Stack:** Laravel 13 + Eloquent, **MySQL/MariaDB** untuk produksi (SKPL TEC-03), SQLite in-memory untuk tes. Tanpa paket pihak ketiga kecuali benar-benar perlu; jika menambah paket, jelaskan alasannya di Log.
3. **Jangan merusak frontend.** Nama field formulir yang sudah dipakai Blade (mis. `title`, `excerpt`, `isi_teks`, `login`) tetap dipakai; pemetaan ke kolom database dilakukan di controller/FormRequest. Perubahan tampilan hanya bila disebut di fase ini.
4. **Validasi di server selalu** lewat FormRequest, dengan pesan Bahasa Indonesia yang sudah ada di controller.
5. **Keamanan:** kata sandi di-hash (bawaan Laravel), query lewat Eloquent/Query Builder (tanpa SQL mentah dari input), keluaran Blade tetap di-escape (`{{ }}`), mass assignment dibatasi `$fillable`, izin dicek di server (Policy/Gate), bukan hanya disembunyikan di menu.
6. **Data pribadi:** jangan menyimpan data yang tidak dibutuhkan. Aspirasi tidak pernah tampil di publik. NIK (bila dikumpulkan) disimpan terenkripsi dan hanya tampil disamarkan.
7. **Publik hanya membaca konten berstatus `terbit`.** Draf tidak boleh bocor lewat daftar, detail, pencarian, atau hitungan.
8. **Hapus jalan pintas demo** (`DemoContent`, `DemoStore*`, `?demo=`, `?peran=`, login lokal) sesuai fasenya; data contoh pindah ke seeder dan diberi tanda "contoh".
9. **Keputusan desa yang belum ada:** pakai nilai bawaan dari tabel "Keputusan & nilai bawaan" di bawah, taruh di `config/desa.php`/`.env` agar mudah diubah, dan catat di Log.
10. **Tes:** setiap fase menambah Feature test. Selesai fase: `php artisan test` lolos semua (termasuk 64 tes FE lama yang disesuaikan), `npm run build` lolos, lalu commit `feat(be): fase N — <ringkasan>`.

---

## Keputusan & nilai bawaan

Konfirmasi ke desa/tim secepatnya. Sampai dikonfirmasi, pakai nilai bawaan ini (semua lewat `config/desa.php` atau `.env`).

| Keputusan | Dibutuhkan di fase | Nilai bawaan sementara |
|---|---|---|
| Versi PHP di hosting | 0 | Lihat Fase 0: `composer.lock` saat ini **butuh PHP ≥ 8.4.1** |
| Database produksi | 0 | MySQL 8 / MariaDB 10.6+, `utf8mb4` |
| Pengirim email (SMTP) untuk atur ulang kata sandi | 2 | `MAIL_MAILER=log` di lokal; SMTP diisi saat rilis |
| Matriks izin Staf ("sesuai penugasan") | 3 | Staf boleh modul yang **ditugaskan** per akun; Akun & Hak Akses hanya Super Admin (sesuai `DemoContent::izin()`) |
| Batas unggahan | 8 | Gambar ≤ 2 MB (JPG/PNG/WebP, maks. 4000 px); PDF ≤ 10 MB |
| Pengumpulan NIK pada aspirasi | 9 | Opsional, terenkripsi, tampil disamarkan; bisa dimatikan `ASPIRASI_NIK=false` |
| Masa simpan aspirasi | 9 | 24 bulan sejak selesai, lalu nama & NIK dianonimkan |
| Balasan aspirasi ke warga | 9 | Catatan internal saja (formulir tidak meminta kontak) |
| Daftar dusun dan jabatan resmi | 10 | "Dusun 1–6" dan daftar jabatan yang sudah ada di FE |
| Siapa menyetujui publikasi | 5–7 | Status `terbit` hanya bisa diset oleh akun dengan izin modul itu; dicatat di audit log |

---

## Temuan dari pemeriksaan FE (diselesaikan di fase terkait)

| # | Temuan | Fase |
|---|---|---|
| 1 | `composer.json` menulis `php: ^8.3`, tetapi `composer.lock` berisi Symfony 8.1 yang **butuh PHP ≥ 8.4.1**. `composer install` gagal di PHP 8.3. | 0 |
| 2 | Rute `/admin/*` belum dilindungi `auth`; login lokal dan `?peran=` masih aktif. | 2–3 |
| 3 | Admin APBDes hanya menerima **total** pendapatan dan belanja, padahal halaman publik menampilkan **rincian per bidang** dan pembiayaan. Tanpa input rincian, tabel publik akan selalu kosong. | 7 |
| 4 | Unggahan belum punya batas ukuran dan hanya dicek dari ekstensi. | 8 |
| 5 | Form akun memakai satu kolom "Email atau username" tanpa validasi email; tautan atur ulang kata sandi butuh email yang valid dan unik. | 3 |
| 6 | Komoditas pertanian belum punya unggahan foto (DESIGN.md §6.4 menyebut foto relevan). | 6 |
| 7 | `npm run build` mengunduh font dari `fonts.bunny.net` saat build; server build/hosting harus punya akses internet, atau font di-host sebagai berkas di repo. | 11 |
| 8 | Hash commit di Log `planFE.md` fase 7–11 tidak sama dengan riwayat git (mis. Log `e31a2f6`, git `df03e19`). Perbarui agar jejaknya benar. | 0 |

---

## Fase 0 — Persiapan: versi PHP, database, alat bantu

- [x] Tentukan versi PHP hosting, lalu samakan proyek:
  - Jika hosting **PHP ≥ 8.4**: ubah `composer.json` menjadi `"php": "^8.4"` dan biarkan lock.
  - Jika hosting **PHP 8.3**: tambahkan `"config": {"platform": {"php": "8.3.x"}}` lalu `composer update` agar lock memilih paket yang cocok; pastikan tes tetap lolos. Catat versi Symfony yang terpasang.
- [x] `.env.example`: contoh koneksi MySQL (`DB_CONNECTION=mysql`, `DB_DATABASE=siprodes`, dst.), tetap SQLite untuk tes di `phpunit.xml`.
- [x] Jalankan `php artisan boost:install` (paket `laravel/boost` sudah ada di `require-dev`) agar Claude Code mendapat panduan dan alat bantu Laravel. Setelah itu blok bootstrap Boost di `CLAUDE.md`/`AGENTS.md` akan diganti otomatis.
- [x] Tambahkan bagian **"Aturan Backend"** di `CLAUDE.md` (ringkasan Aturan kerja di atas + lokasi `docs/planBE.md`).
- [x] Buat `config/desa.php` berisi nilai bawaan dari tabel Keputusan (batas unggahan, NIK aktif, masa simpan aspirasi, nama desa).
- [x] Perbaiki hash commit di Log `planFE.md` (temuan #8).

**Checkpoint:** `composer install` bersih di versi PHP yang dipilih, `php artisan test` (64 tes) lolos, laporan versi PHP/Laravel/Symfony/database.

---

## Fase 1 — Skema database, model, factory, seeder

Rancang dari bentuk data di `DemoContent`, `DemoStoreKoleksi`, dan aturan validasi controller.

- [x] Migrasi dan model (nama tabel Bahasa Indonesia, snake_case):

| Tabel | Kolom utama | Catatan |
|---|---|---|
| `users` (ubah) | `username` unik nullable, `peran` (`super_admin`/`staf`), `aktif` bool, `terakhir_masuk_at` | email wajib & unik |
| `modul_akses` | `user_id`, `modul` | penugasan modul untuk Staf |
| `profil_desa` | `sejarah`, `visi`, `misi` (json), `alamat`, `lintang`, `bujur`, `telepon`, `email`, `jam_layanan`, `kontak_terverifikasi`, `penduduk`, `kk`, `luas_ha`, `jumlah_dusun`, `sumber_data`, `data_terverifikasi`, `status_*` | satu baris |
| `dusun`, `jabatan` | `nama`, `urutan` | data master |
| `perangkat_desa` | `nama`, `jabatan_id`, `foto_path`, `urutan`, `tampil` | |
| `berita` | `judul`, `slug` unik, `kategori`, `ringkasan`, `isi` (teks penanda ringan), `gambar_path`, `gambar_alt`, `tanggal`, `status`, `penulis_id`, `diterbitkan_at` | index `status, tanggal` |
| `agenda` | `judul`, `tanggal`, `waktu` null, `lokasi` null, `deskripsi`, `status` | |
| `komoditas` | `nama`, `deskripsi`, `musim_panen`, `foto_path`, `foto_alt`, `unggulan`, `status` | |
| `kelompok_tani` | `nama`, `komoditas_id`, `dusun_id`, `ketua`, `kontak`, `kontak_disetujui` | |
| `umkm` | `nama`, `produk`, `deskripsi`, `foto_path`, `foto_alt`, `whatsapp`, `whatsapp_disetujui`, `status` | |
| `apbdes_periode` | `tahun` unik, `periode`, `status`, `diterbitkan_at` | |
| `apbdes_rincian` | `periode_id`, `jenis` (`pendapatan`/`belanja`/`pembiayaan`), `uraian`, `anggaran` (bigint rupiah), `urutan` | |
| `apbdes_dokumen` | `periode_id`, `judul`, `path`, `ukuran`, `diunggah_oleh` | disk privat |
| `aspirasi` | `nama`, `nik` (cast `encrypted`, null), `pesan`, `status` (`baru`/`dibaca`/`selesai`), `dibaca_at`, `selesai_at` | tanpa IP mentah |
| `aspirasi_riwayat` | `aspirasi_id`, `user_id`, `aksi`, `catatan` | |
| `log_aktivitas` | `user_id`, `aksi`, `subjek_type`, `subjek_id`, `keterangan`, `ip` | |

- [x] Enum PHP untuk status (`StatusTerbit`, `StatusAspirasi`, `Peran`) dan scope `terbit()` di model konten.
- [x] Factory untuk semua model; seeder `DataContohSeeder` memindahkan isi `DemoContent`/`DemoStoreKoleksi` (ditandai "contoh"), dan `AdminAwalSeeder` membuat satu Super Admin dari `.env` (`ADMIN_EMAIL`, kata sandi acak dicetak sekali ke konsol).
- [x] Belum menyambungkan controller (dilakukan per fase).

**Checkpoint:** `php artisan migrate:fresh --seed` berhasil di SQLite dan MySQL; tes relasi dan scope `terbit()`.

---

## Fase 2 — Autentikasi admin (FR-07, NFR-02)

- [x] `AdminAuthController@masuk`: login dengan **email atau username** + kata sandi, hanya akun `aktif`, `Auth::attempt` + `session()->regenerate()`, "ingat saya".
- [x] Pembatasan percobaan: `RateLimiter` per (login + IP), mis. 5 kali per menit lalu kunci 15 menit; pesan tetap umum (tidak membocorkan akun mana yang ada). Catat `terakhir_masuk_at`.
- [x] `keluar`: logout, `invalidate()`, `regenerateToken()`.
- [x] Lupa kata sandi: broker `Password` Laravel, notifikasi email Bahasa Indonesia, halaman **buat kata sandi baru** (tambahkan view sederhana dengan komponen yang ada), respons sama untuk email terdaftar/tidak.
- [x] Middleware: `guest` untuk masuk/lupa kata sandi, `auth` untuk seluruh `/admin/*` lainnya; redirect tamu ke `admin.login`.
- [x] Hapus jalan pintas login lokal di `AdminAuthController`.
- [x] Kebijakan kata sandi: minimal 10 karakter (`Password::min(10)`), tidak sama dengan email.

**Checkpoint:** tes: tamu diarahkan ke login, login benar/salah, akun nonaktif ditolak, penguncian setelah gagal berulang, alur atur ulang kata sandi, logout.

---

## Fase 3 — Otorisasi peran & modul Akun (NFR-03)

- [x] `Peran::saatIni()` membaca `auth()->user()->peran`; hapus `?peran=` dan peran contoh.
- [x] Gate/Policy: Super Admin semua modul; Staf hanya modul di `modul_akses`. Terapkan `can:` pada rute/controller, `@can` di sidebar (`Navigation::admin()`), 403 untuk akses langsung.
- [x] Modul Akun (khusus Super Admin): tambah akun (email **wajib & unik**, username opsional, peran, modul untuk Staf), aktif/nonaktif, ubah peran, kirim tautan "buat kata sandi" (token broker), tanpa admin mengisi kata sandi. Cegah menonaktifkan diri sendiri dan Super Admin terakhir.
- [x] Matriks izin di halaman Akun dibaca dari konfigurasi/database, bukan `DemoContent::izin()`.

**Checkpoint:** tes per peran: Staf tanpa penugasan mendapat 403 di setiap modul; Staf dengan penugasan bisa; Akun hanya Super Admin; Super Admin terakhir tidak bisa dinonaktifkan.

---

## Fase 4 — Halaman publik membaca database (FR-01–FR-05)

- [x] `PublicPageController` dan view composer memakai model, bukan `DemoContent`: Beranda, Profil, Potensi, Berita (filter kategori, pencarian `q`, paginasi), Detail (404 untuk draf/tidak ada), Kontak, APBDes (bagian ringkas; rincian diselesaikan Fase 7).
- [x] Pencarian Potensi lintas halaman di server (`?q=`), menggantikan penyaringan di klien (`resources/js/app.js`, temuan di laporanFE §4.5).
- [x] Kontak, alamat, peta, WhatsApp, dan kontak kelompok tani hanya tampil bila kolom persetujuan/terverifikasi bernilai benar.
- [x] Hindari N+1 (`with()`), tambah index yang dipakai filter.
- [x] Hapus `?demo=` dari kode produksi; keadaan kosong/gagal tetap teruji lewat data tes.

**Checkpoint:** tes: draf tidak muncul di daftar, detail, pencarian, dan hitungan; kontak tidak tampil sebelum diverifikasi; paginasi & filter berfungsi.

---

## Fase 5 — Admin A: Berita, Agenda, Ringkasan (FR-11)

- [x] Berita: CRUD ke database (slug unik otomatis, status draf/terbit, `diterbitkan_at`), cari/filter/paginasi dari query.
- [x] Isi berita: simpan teks penanda ringan, render di server dengan `league/commonmark` (sudah terpasang bersama Laravel) memakai `html_input = strip` dan `allow_unsafe_links = false`; uji bahwa `<script>` tidak pernah dirender.
- [x] Gambar utama lewat layanan unggahan (sementara sederhana; dirapikan di Fase 8), alt wajib bila ada gambar.
- [x] Agenda: CRUD, status, filter waktu (mendatang/lewat).
- [x] Ringkasan dashboard: semua angka dihitung dari database (berita terbit/draf, UMKM, data pertanian, aspirasi baru), tanpa angka statis.

**Checkpoint:** tes CRUD + otorisasi; konten yang diterbitkan langsung tampil di publik, draf tidak.

---

## Fase 6 — Admin B: Profil, Struktur, Potensi (FR-08, FR-10)

- [x] Profil (4 tab) ke tabel `profil_desa`; kontak/lokasi hanya tampil publik bila "sudah diverifikasi" dicentang.
- [x] Struktur perangkat: CRUD, urutan naik/turun (transaksi), foto 1:1, toggle tampil.
- [x] Komoditas: CRUD + **foto dan alt** (temuan #6; tambahkan uploader di form admin yang ada).
- [x] Kelompok tani: CRUD; kontak wajib bila dicentang disetujui; tampil publik hanya bila disetujui.
- [x] UMKM: CRUD; WhatsApp & foto tampil publik hanya bila disetujui; normalisasi nomor (format `62…` untuk tautan `wa.me`).

**Checkpoint:** tes CRUD + aturan persetujuan + urutan struktur.

---

## Fase 7 — APBDes: periode, rincian, dokumen PDF (FR-03, FR-09)

- [x] **Tambahkan input rincian** di halaman admin APBDes (temuan #3): baris pendapatan per sumber, belanja per bidang, dan pembiayaan (uraian bawaan dari daftar yang dipakai FE, bisa ditambah). Pakai komponen tabel/field yang sudah ada; total dihitung otomatis dari rincian, bukan diketik.
- [x] CRUD periode (tahun unik), status draf/terbit; publik hanya menampilkan periode terbit.
- [x] Dokumen PDF: simpan di **disk privat**, rute unduhan `apbdes.unduh` yang memeriksa status terbit dan mengirim berkas dengan nama aman (`Content-Disposition: attachment`). Aktifkan tombol "Unduh PDF" di publik.
- [x] Diagram pendamping muncul otomatis bila total terisi (logika FE sudah ada).

**Checkpoint:** tes: total = jumlah rincian, PDF draf tidak bisa diunduh publik, unduhan PDF terbit berhasil, berkas bukan PDF ditolak.

---

## Fase 8 — Unggahan berkas & gambar (terpusat, TEC-06)

- [x] Satu layanan `UnggahBerkas` untuk semua modul: nama berkas acak (`hashName`), folder per modul, disk `public` untuk gambar dan `local` (privat) untuk PDF.
- [x] Validasi di server: `mimetypes` (bukan hanya ekstensi), batas ukuran dari `config/desa.php`, dimensi gambar maksimum, cek tanda tangan berkas PDF (`%PDF-`); pesan galat sama seperti uploader FE.
- [x] Hapus berkas lama saat diganti atau datanya dihapus.
- [x] `php artisan storage:link` didokumentasikan; tidak ada berkas unggahan yang bisa dieksekusi dari web.

**Checkpoint:** tes: berkas terlalu besar ditolak, berkas yang diganti ekstensinya ditolak, berkas lama terhapus.

---

## Fase 9 — Ruang Aspirasi (FR-06, FR-12)

- [ ] `AspirasiController@store` menyimpan nama, pesan, status `baru`; NIK hanya bila `ASPIRASI_NIK=true`, disimpan terenkripsi.
- [ ] Anti-spam: `throttle` per IP (sudah ada, sesuaikan), **honeypot** tersembunyi, dan waktu isi minimum; pesan galat tetap umum.
- [ ] Admin: daftar & filter status dari database, detail menandai `dibaca` otomatis saat dibuka (atau lewat tombol, sesuai FE), ubah status, catatan tindak lanjut, riwayat di `aspirasi_riwayat`; NIK tampil lewat `Format::nikSamar()`.
- [ ] Badge jumlah aspirasi baru di sidebar dari database.
- [ ] Perintah terjadwal `aspirasi:anonimkan` sesuai masa simpan (`config/desa.php`), didaftarkan di scheduler.
- [ ] Pastikan tidak ada jalur publik yang menampilkan isi aspirasi (tes yang ada tetap lolos).

**Checkpoint:** tes: simpan aspirasi, NIK terenkripsi di database, honeypot menolak bot, status & riwayat tercatat, anonimisasi berjalan.

---

## Fase 10 — Audit log, pengamanan & data master (NFR-02)

- [ ] `log_aktivitas` untuk tindakan sensitif: masuk/keluar, gagal masuk berulang, ubah akun/peran, terbitkan/hapus konten, unggah/hapus dokumen APBDes, ubah status aspirasi. Tampilkan daftar sederhana di halaman Akun (Super Admin).
- [ ] Middleware header keamanan: `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, `Content-Security-Policy` dasar yang tetap mengizinkan aset Vite dan Alpine.
- [ ] Produksi: `APP_DEBUG=false`, paksa HTTPS, cookie sesi `secure` + `http_only` + `same_site=lax`.
- [ ] Data master dusun & jabatan dari tabel (bukan daftar tetap di controller); halaman kelola sederhana untuk Super Admin, atau seeder bila desa belum memberi data.
- [ ] Hapus `app/Support/DemoContent.php`, `DemoStore.php`, `DemoStoreKoleksi.php` bila sudah tidak dipakai; `grep -rn "TODO(backend)"` harus kosong atau tiap sisa dijelaskan di Log.

**Checkpoint:** tes header, tes audit log, tidak ada referensi `Demo*` tersisa.

---

## Fase 11 — Pengujian akhir, panduan rilis & laporan

- [ ] Tes keamanan: brute force login, akses per peran ke setiap rute admin, XSS pada semua input teks, unggahan berbahaya, CSRF, pencarian dengan karakter khusus (SQL injection).
- [ ] Uji ulang FE: `php artisan test` penuh, `npm run build`, cek manual alur utama di browser dengan data dari database.
- [ ] Performa: periksa query (N+1) di Beranda, Berita, Potensi, APBDes; ukur ulang Lighthouse Beranda di server yang mirip produksi.
- [ ] Font: putuskan apakah font di-host sebagai berkas di repo atau build butuh internet (temuan #7).
- [ ] `docs/deploy.md`: versi PHP & ekstensi, MySQL, `.env` produksi, `storage:link`, cron `schedule:run`, antrean (atau `sync`), SMTP, HTTPS, **backup harian database + `storage/app`** dan cara pemulihan (NFR-07), langkah membuat Super Admin pertama.
- [ ] `docs/laporanBE.md`: fitur yang selesai per FR/NFR, keputusan yang masih memakai nilai bawaan, hasil tes, dan risiko yang tersisa.

**Checkpoint:** laporan ditinjau tim; semua fase berstatus `Selesai`.

---

## Log

| Tanggal | Fase | Ringkasan | Commit |
|---|---|---|---|
| 2026-11-03 | 0 | `composer.json` `php: ^8.4` (asumsi hosting PHP ≥ 8.4, **belum dikonfirmasi**; lock memakai Symfony 8.1.9 yang butuh ≥ 8.4.1) dan `composer.lock` diperbarui hanya pada content-hash/constraint PHP. `.env.example` memakai MySQL (`siprodes`), `phpunit.xml` tetap SQLite memori. `boost:install --guidelines --skills --mcp` dijalankan (menambah `.claude/skills`, `.mcp.json`, `boost.json`, dan mengganti blok Boost di `CLAUDE.md`; Aturan Frontend tetap utuh); bagian "Aturan Backend" ditambahkan. `config/desa.php` (nama desa, batas unggahan, NIK, masa simpan aspirasi, waktu isi minimum, batas login). Hash commit di Log `planFE.md` fase 7–11 diperbaiki. Versi: PHP 8.5.8, Laravel 13.38.0, Symfony 8.1.9; 64 tes lolos. Catatan: `composer install` diuji dengan `--dry-run`; MySQL belum dijalankan (diuji mulai Fase 1). | (lihat git) |
| 2026-11-03 | 1 | 6 berkas migrasi (alter `users` + `modul_akses`; profil/struktur; berita/agenda; potensi; APBDes; aspirasi/log), 17 model, enum `StatusTerbit`/`StatusAspirasi`/`Peran`/`Modul`/`JenisAnggaran`, trait `MemilikiStatusTerbit` (scope `terbit()`/`draf()`), 17 factory, seeder `MasterDataSeeder` (dusun 1–6, 8 jabatan, profil desa; aman di produksi), `AdminAwalSeeder` (dari `ADMIN_EMAIL`, kata sandi acak dicetak sekali), `DataContohSeeder` (semua baris `contoh = true`, tidak jalan di produksi). Penyimpangan dari rancangan: kolom `contoh` boolean ditambahkan di tabel konten untuk menandai data contoh; `profil_desa` memakai `visi_misi_resmi`, `perangkat_resmi`, `alamat_wilayah`, `mata_pencaharian` (pengganti `status_*`); `aspirasi.dianonimkan_at` dan `apbdes_dokumen.keterangan_periode` ditambahkan; `agenda.waktu` bertipe `time`; seeder tidak mengisi pembiayaan APBDes (FE belum punya daftarnya, Fase 7). Tanpa paket baru. Controller belum disambungkan. 12 tes baru (total 76 lolos), `npm run build` lolos, `migrate:fresh --seed` dan rollback/migrate ulang di SQLite berhasil. **Belum diuji di MySQL** (tidak ada server MySQL di mesin ini). | (lihat git) |
| 2026-11-03 | 2 | Login email atau username (`Auth::attempt` + `aktif`, regenerasi sesi, "ingat saya", `terakhir_masuk_at`); pembatas percobaan per (login + IP) lewat `RateLimiter`: 5 gagal dalam 15 menit mengunci login (nilai di `config/desa.php`), pesan gagal tetap umum, hitungan direset saat berhasil; `throttle:30,1` di rute sebagai pembatas kasar. Logout (`invalidate` + `regenerateToken`). Lupa kata sandi lewat broker `Password`: tautan hanya dikirim ke akun **aktif**, respons selalu sama; email Bahasa Indonesia (`ResetPassword::toMailUsing`/`createUrlUsing` di `AppServiceProvider`), berlaku 60 menit; halaman baru `atur-ulang-kata-sandi` (rute `admin.password.reset`/`password.update`); token sekali pakai, akun nonaktif ditolak. Kebijakan kata sandi: min. 10 karakter, tidak sama dengan email, konfirmasi. Middleware `guest` untuk masuk/lupa/atur ulang, `auth` + `PastikanAkunAktif` (akun yang dinonaktifkan langsung keluar) untuk semua `/admin/*` lainnya; tamu diarahkan ke `admin.login`. Jalan pintas login lokal dihapus; nama pengguna di layout admin kini dari akun yang masuk. Tiga FormRequest di `app/Http/Requests/Admin`. Tes: `Fase2AutentikasiTest` (17), tes admin lama disesuaikan agar login lewat `tests/Concerns/MasukSebagaiAdmin`, `Fase8AdminTest` ditulis ulang (tes login-demo diganti tes sungguhan). Total 91 tes lolos, `npm run build` lolos. Catatan: `?peran=` dan `DemoContent::admin()` untuk peran masih ada sampai Fase 3; SMTP belum dikonfigurasi (`MAIL_MAILER=log` di lokal), pengiriman email sungguhan belum diuji; tombol Keluar di UI memakai rute yang sama (POST `admin.logout`). | (lihat git) |
| 2026-11-03 | 3 | Peran dari `auth()->user()->peran` (`Peran::saatIni()`); `?peran=`, `DemoContent::admin()/izin()` dan data akun contoh dihapus. Izin lewat Gate `akses-modul` (akun harus aktif; Super Admin semua modul; Staf hanya modul di `modul_akses`) dan middleware `modul:*` (`PastikanPunyaModul`) yang membungkus tiap grup rute admin: berita (Berita+Agenda), potensi (pertanian, kelompok tani, UMKM), profil (Profil+Struktur), apbdes, aspirasi, akun; Dashboard terbuka untuk semua akun yang masuk. Sidebar disaring dengan Gate (`Navigation::admin()` memakai kunci `modul`, grup kosong disembunyikan); pintasan dan kartu/daftar Aspirasi di Dashboard juga hanya tampil bila berhak. Modul Akun nyata (`User`, `SimpanAkunRequest`): tambah akun (email wajib & unik, username opsional/unik, peran, modul untuk Staf), ubah, aktif/nonaktif, kirim tautan atur ulang; akun baru dibuat dengan kata sandi acak lalu dikirimi tautan broker (admin tidak pernah mengisi kata sandi). Pelindung: tidak boleh menonaktifkan diri sendiri; tidak boleh menonaktifkan/menurunkan Super Admin aktif terakhir. Matriks izin di halaman Akun dibangun dari enum `Modul`. Keputusan bawaan dipakai: Staf hanya modul yang ditugaskan dan **Akun & Hak Akses khusus Super Admin walau ada baris penugasan** (nilai bawaan, perlu konfirmasi desa; berbeda dari `DemoContent::izin()` lama yang memberi Staf “Ya” pada Berita/Potensi/Aspirasi — kini semuanya lewat penugasan). Rute akun memakai binding `{user}` (bukan lagi id demo). 12 tes baru `Fase3OtorisasiTest` (total 103 lolos), tes Fase8/10 yang bergantung peran demo dihapus/diganti, `npm run build` lolos. Catatan: izin per-aksi di dalam modul (mis. hanya baca) tidak dibedakan; kartu statistik Dashboard lain (berita/UMKM/pertanian) tampil untuk semua akun (hanya angka, tanpa isi). | (lihat git) |
| 2026-11-03 | 4 | Halaman publik (Beranda, Profil, Potensi, Berita, Detail, Kontak, APBDes bagian ringkas) membaca database lewat `App\Support\KontenPublik` (scoped singleton; keluaran berbentuk sama dengan `DemoContent` sehingga Blade nyaris tidak berubah) dan `PublicPageController`. Hanya konten `terbit` yang keluar (scope `terbit()`; kategori, pencarian, paginasi, hitungan, dan “berita lainnya” juga hanya dari yang terbit). Kontak/alamat/jam layanan/email/telepon/peta hanya bila `kontak_terverifikasi`; kontak kelompok tani dan WhatsApp UMKM hanya bila disetujui (nilainya tidak pernah dikirim ke view bila belum disetujui). Pencarian Potensi (`?q=`) kini di server lintas halaman (komoditas, kelompok tani termasuk nama komoditasnya, UMKM) lewat trait `DapatDicari` (LIKE ter-escape + binding) dan formulir GET yang mempertahankan tab; JS klien (`cocok/hitung/kosong`) dihapus. Berita: paginasi 4/halaman, filter kategori, pencarian judul/ringkasan. APBDes: tahun hanya dari periode terbit, total dari rincian, keadaan kosong baru (`apbdes-kosong`) dan keadaan gagal dimuat kini muncul saat query database gagal (`QueryException` → `apbdes-gagal`, dilaporkan lewat `report()`); tautan unduh PDF tetap `null` sampai Fase 7. `?demo=` dan keadaan “memuat” berita dihapus. Footer/logo/strip wilayah memakai `KontenPublik::identitas()`; nama wilayah dipindah ke `config/desa.php` (`wilayah`). Beranda tahan data kosong (tanpa komoditas unggulan/UMKM/berita/agenda). Catatan penyimpangan: `peluang` komoditas tidak ada kolomnya sehingga kosong; status “resmi” (penanda data contoh) kini diturunkan dari kolom `contoh`/flag verifikasi; sejarah dipecah per paragraf (paragraf pertama menjadi lead); penulis berita publik selalu “Admin desa” (nama akun tidak dibuka). `DemoContent` publik masih dipakai controller admin (dihapus bertahap Fase 5–10). Tes baru `Fase4PublikDatabaseTest` (14: draf tidak bocor di daftar/detail/pencarian/hitungan, kontak sebelum/sesudah verifikasi, persetujuan kontak, pencarian lintas halaman & karakter khusus, total APBDes, jumlah query tidak naik seiring data); tes publik lama disesuaikan memakai seeder. Total 119 tes lolos, `npm run build` lolos. | (lihat git) |
| 2026-11-03 | 5 | **Berita**: CRUD ke database (`AdminBeritaController`, `SimpanBeritaRequest`); slug otomatis dari judul dan unik (`-2`, `-3`), tidak berubah saat judul diubah; status ditentukan tombol Simpan; `diterbitkan_at` diisi saat pertama terbit (tidak berubah saat disimpan ulang, dikosongkan saat kembali draf); `penulis_id` dari akun; daftar admin dengan cari/filter status/kategori/paginasi dari query. **Isi**: teks penanda ringan dirender di server oleh `App\Support\TeksRingan` (`league/commonmark`, `html_input=strip`, `allow_unsafe_links=false`); teruji `<script>`, `onerror`, dan `javascript:` tidak pernah dirender; gaya `.isi-berita` di `app.css`; waktu baca dari teks polos. **Gambar utama**: `App\Support\UnggahBerkas` sederhana (nama acak, folder `berita/`, disk `public`, dihapus saat diganti/dicentang hapus/berita dihapus); validasi `mimetypes` JPG/PNG/WebP, ≤ 2 MB, ≤ 4000 px (dari `config/desa.php`), alt wajib bila ada gambar (juga bila gambar lama dipertahankan); gambar tampil di detail publik (fallback placeholder). **Agenda**: CRUD ke database (`SimpanAgendaRequest`), filter mendatang/lewat/semua, status, cari; rute `{id}` dibatasi angka. **Ringkasan**: semua angka dari database (berita terbit/draf, UMKM, komoditas + kelompok tani, aspirasi baru, aspirasi terbaru, draf terakhir); tiap kartu/daftar hanya dihitung dan ditampilkan bila akun berhak atas modulnya (Staf tanpa modul Aspirasi tidak menerima nama/pesan aspirasi); badge Aspirasi di sidebar dari database. Penyimpangan: halaman admin Aspirasi masih memakai `DemoStore` sampai Fase 9, jadi perubahan status di sana belum memengaruhi angka Ringkasan; `?demo=kosong` admin dihapus; `DemoStore` hanya menyisakan koleksi generik, `DemoContent` kehilangan `identitas/statistik/beranda/berita/agenda`; kolom `kategori` dibatasi 50 karakter (sesuai skema). 17 tes baru di `Fase5AdminKontenTest` (menggantikan `Fase9AdminTest`); total 131 tes lolos, `npm run build` lolos. | (lihat git) |
| 2026-11-03 | 6 | **Profil** (4 tab) ke `profil_desa` lewat `SimpanProfilRequest`: sejarah, visi/misi (butir kosong dibuang; menyimpan visi menandai naskah “resmi” sehingga penanda “Menunggu naskah resmi” hilang), data ringkas (+ sumber, flag verifikasi → tanda “±”), lokasi. Kontak/alamat/peta hanya tampil publik bila disimpan lewat **Simpan & Terbitkan** dengan kotak verifikasi dicentang (“Simpan Draf” tidak menayangkannya); tautan Google Maps dari koordinat. **Struktur**: CRUD ke `perangkat_desa`, jabatan dari tabel master, foto 1:1 (aturan `dimensions:ratio=1/1`) di disk `public/perangkat`, urutan naik/turun dalam transaksi dengan penomoran ulang (tahan nilai kembar), toggle tampil; foto publik diberi alt “Foto {nama}, {jabatan}”; status “resmi” perangkat kini diturunkan dari data (tidak ada baris `contoh`). **Komoditas**: CRUD + foto & alt (temuan #6) + kotak “unggulan”; foto tampil di Potensi/Beranda. **Kelompok tani**: CRUD, pilihan komoditas/dusun memakai id dari database, kontak wajib bila dicentang disetujui, kontak publik hanya bila disetujui. **UMKM**: CRUD + status Draf/Terbit + foto & alt; nomor WhatsApp dinormalkan ke `62…` (`Format::nomorWa`, ditolak bila bukan nomor seluler Indonesia); foto dan WhatsApp memakai satu persetujuan pemilik usaha dan hanya tampil publik bila disetujui. Aturan/pesan validasi gambar dipusatkan di `UnggahBerkas::aturanGambar()/pesanGambar()` (dipakai juga Berita; diperluas di Fase 8). Rute `{id}` modul-modul ini dibatasi angka. Penyimpangan: `PublicPageController` memakai `app(KontenPublik::class)` per permintaan (bukan constructor injection) karena instance controller dipakai ulang antar-permintaan dalam tes; sisa data contoh `DemoContent::profil/potensi` dan koleksi demo struktur/komoditas/kelompok/umkm dihapus (tersisa APBDes dan Aspirasi); kolom `perangkat_resmi` di `profil_desa` tidak lagi dipakai. 18 tes baru `Fase6AdminProfilPotensiTest` (+ tes lama profil/struktur/potensi di `Fase10AdminTest` diganti); total 143 tes lolos, `npm run build` lolos. | (lihat git) |
| 2026-11-03 | 7 | **Rincian**: halaman admin APBDes ditulis ulang (temuan #3) — editor baris uraian + anggaran untuk pendapatan, belanja, dan pembiayaan (Alpine `rincianApbdes`), uraian bawaan dari daftar FE untuk periode baru (pembiayaan: satu baris “Pembiayaan netto”), total ditampilkan otomatis dan **dihitung server dari rincian** (tidak diketik); rincian diganti seluruhnya per simpan dalam transaksi; baris kosong dibuang; angka hanya format Indonesia bilangan bulat (`12.500.000`). **Periode**: CRUD (`SimpanApbdesRequest`), tahun 2000–2100 dan unik, status draf/terbit dengan `diterbitkan_at`; publik hanya periode terbit; data yang diinput admin menjadi `contoh = false` sehingga penanda “Data contoh” hilang; diagram otomatis muncul bila total terisi. **Dokumen PDF**: disimpan di **disk privat** (`local`, `storage/app/private/apbdes`, nama acak), metadata (ukuran, pengunggah); validasi `mimetypes:application/pdf`, ≤ 10 MB (`config/desa.php`), dan cek tanda tangan `%PDF-` (berkas yang diganti ekstensi/dikirim sebagai skrip atau PNG ditolak, pesan “Ditolak: hanya PDF yang diizinkan…”); judul wajib bila berkas dipilih; hapus dokumen/periode ikut menghapus berkas. **Unduhan**: rute publik `apbdes.unduh` hanya melayani dokumen milik periode terbit (draf/tanpa berkas/berkas hilang → 404), `Content-Disposition: attachment` dengan nama aman (slug judul), `X-Content-Type-Options: nosniff`; admin punya rute unduh sendiri (termasuk draf, di balik `modul:apbdes`). Tombol “Unduh PDF” publik kini aktif. `UnggahBerkas` ditambah `simpanPdf/hapusPdf/aturanPdf/pesanPdf/unduhPdf`. Penyimpangan: pembiayaan hanya satu angka netto (kolom anggaran tidak bertanda negatif); angka desimal sen tidak diterima lagi; `DemoContent::apbdes` dan koleksi demo APBDes dihapus (tersisa Aspirasi). 12 tes baru `Fase7ApbdesTest` (tes APBDes lama di `Fase10AdminTest` diganti); total 155 tes lolos, `npm run build` lolos. | (lihat git) |
| 2026-11-03 | 8 | `App\Support\UnggahBerkas` dijadikan layanan terpusat (TEC-06) untuk Berita, Struktur, Komoditas, UMKM, dan APBDes: nama berkas acak 40 karakter dan **ekstensi ditentukan dari isi** (bukan nama kiriman), satu folder per modul (`berita/`, `perangkat/`, `komoditas/`, `umkm/`, `apbdes/`), gambar ke disk `public`, PDF ke disk `local` (privat). **Gambar ditulis ulang** dengan GD (JPG/PNG/WebP): hanya piksel yang tersimpan sehingga EXIF/GPS, komentar, dan muatan tersembunyi (mis. kode PHP yang ditempel di akhir JPEG — teruji) hilang; orientasi EXIF dikoreksi; gambar dikecilkan ke sisi panjang 1920 px (`UNGGAH_GAMBAR_SISI_PANJANG`, hemat bandwidth); penjaga memori mencegah gambar raksasa menghabiskan memori PHP (galat validasi, bukan fatal). Validasi: `mimetypes` dari isi berkas (SVG/PHP/`.phtml` ditolak), ukuran dan dimensi dari `config/desa.php`, PDF cek `%PDF-` + `mimetypes` + ukuran; **pesan galat disamakan dengan uploader FE** (“Ditolak: hanya … yang diizinkan. Pilih berkas lain.” / “Ditolak: ukuran berkas melebihi N MB. Pilih berkas lain.”), dan semua uploader admin kini menerima `maxMb` dari konfigurasi. Berkas lama dihapus saat diganti atau datanya dihapus di semua modul (teruji). Penyimpanan: disk `local` tidak lagi punya rute web (`serve => false`), `storage/app/public/.htaccess` (tidak boleh eksekusi skrip, tanpa indeks direktori; `.gitignore` diubah agar ikut ter-commit), `php artisan storage:link --force` ditambahkan ke skrip `composer setup`. 13 tes baru `Fase8UnggahanTest` (+ pesan galat di tes Fase 5/7 disesuaikan, satu tes Fase 4 yang kadang gagal karena teks acak dibuat stabil); total 168 tes lolos, `npm run build` lolos. Catatan: tidak ada perintah pembersih berkas yatim (berkas yang tertinggal bila proses gagal di tengah); `.htaccess` hanya berlaku di Apache — untuk Nginx aturan setara dicatat di panduan rilis (Fase 11); batas ukuran unggah PHP (`upload_max_filesize`, `post_max_size`) dan `memory_limit` (disarankan ≥ 256M untuk gambar besar) harus disetel di server. | (lihat git) |
