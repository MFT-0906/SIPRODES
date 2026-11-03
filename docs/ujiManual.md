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
