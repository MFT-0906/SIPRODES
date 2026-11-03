# deploy.md — Panduan Rilis Website Desa Marga Agung

Panduan memasang aplikasi di server produksi. Nilai bawaan yang masih menunggu keputusan desa ada di `docs/planBE.md` (tabel "Keputusan & nilai bawaan") dan `docs/laporanBE.md`.

## 1. Persyaratan server

| Komponen | Syarat |
|---|---|
| PHP | **≥ 8.4.1** (`composer.lock` memakai Symfony 8.1) |
| Ekstensi PHP | `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `gd` (JPEG, PNG, WebP), `exif`, `ctype`, `tokenizer`, `xml`, `curl` |
| Database | MySQL 8 / MariaDB 10.6+ (`utf8mb4`) |
| Web server | Nginx atau Apache, dokumen root ke folder `public/` |
| Lainnya | Composer 2, HTTPS (sertifikat valid), akses cron |
| Build frontend | Node 20+ **hanya di mesin build** (tidak wajib di server). Font sudah di-host di repo, build tidak butuh internet. |

Pengaturan PHP (`php.ini`/pool FPM):

```ini
upload_max_filesize = 12M
post_max_size = 14M
memory_limit = 256M      ; pemrosesan gambar besar
max_execution_time = 60
```

## 2. Pemasangan pertama

```bash
git clone <repo> /var/www/desa && cd /var/www/desa
composer install --no-dev --optimize-autoloader
cp .env.example .env          # lalu isi sesuai bagian 3
php artisan key:generate
php artisan migrate --force
php artisan db:seed --class=MasterDataSeeder --force   # dusun 1–6, jabatan, profil kosong
php artisan storage:link
npm ci && npm run build       # atau unggah folder public/build dari mesin build
php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan event:cache
```

Jangan menjalankan `php artisan db:seed` tanpa argumen di produksi untuk data contoh: `DatabaseSeeder` memang melewatkannya di lingkungan production, tetapi gunakan seeder per kelas agar jelas.

Izin folder: `storage/` dan `bootstrap/cache/` harus dapat ditulis pengguna web server (mis. `chown -R www-data:www-data storage bootstrap/cache`).

### Super Admin pertama

```bash
# di .env: ADMIN_EMAIL=nama@domain.desa.id   (ADMIN_NAMA opsional)
php artisan db:seed --class=AdminAwalSeeder --force
```

Kata sandi acak **dicetak sekali** di konsol: catat, login, lalu minta pengguna mengganti lewat menu "Lupa kata sandi". Akun lain dibuat Super Admin dari menu *Akun & Hak Akses* (pengguna menerima tautan untuk membuat kata sandi sendiri, jadi SMTP harus berfungsi).

## 3. `.env` produksi

```dotenv
APP_NAME="Desa Marga Agung"
APP_ENV=production
APP_DEBUG=false                 # dipaksa mati di produksi, tetapi isi tetap false
APP_URL=https://domain-desa.id
APP_TIMEZONE=Asia/Jakarta
APP_KEY=                        # php artisan key:generate (SIMPAN cadangan kunci ini)

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=siprodes
DB_USERNAME=...
DB_PASSWORD=...

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
CACHE_STORE=database
QUEUE_CONNECTION=sync           # tidak ada antrean yang perlu worker

MAIL_MAILER=smtp
MAIL_HOST=...
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_FROM_ADDRESS=no-reply@domain-desa.id
MAIL_FROM_NAME="${APP_NAME}"

ADMIN_EMAIL=...                 # hanya untuk AdminAwalSeeder
TRUSTED_PROXIES=                # isi IP proxy (koma) atau * bila HTTPS ditangani proxy/hosting

# Keputusan desa (nilai bawaan boleh dipakai dulu)
ASPIRASI_NIK=true
ASPIRASI_MASA_SIMPAN_BULAN=24
UNGGAH_GAMBAR_MAKS_KB=2048
UNGGAH_PDF_MAKS_KB=10240
```

`APP_KEY` dipakai mengenkripsi NIK aspirasi: **kehilangan kunci berarti NIK tak terbaca lagi**. Simpan salinannya terpisah dari server.

Setelah mengubah `.env`: `php artisan config:cache`.

## 4. Web server

### Nginx

```nginx
server {
    listen 443 ssl http2;
    server_name domain-desa.id;
    root /var/www/desa/public;
    index index.php;
    client_max_body_size 14M;

    location / { try_files $uri $uri/ /index.php?$query_string; }

    # Berkas unggahan: sajikan sebagai statis, JANGAN pernah dieksekusi.
    location ^~ /storage/ {
        location ~ \.(php|phtml|phar|pl|py|cgi|sh)$ { return 403; }
        autoindex off;
        expires 30d;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
    }

    location ~ /\.(?!well-known) { deny all; }
}
server { listen 80; server_name domain-desa.id; return 301 https://$host$request_uri; }
```

### Apache

Aktifkan `mod_rewrite` dan `AllowOverride All` untuk `public/`. Berkas `storage/app/public/.htaccess` (ikut repo) sudah melarang eksekusi skrip di folder unggahan.

Header keamanan (CSP, HSTS, X-Frame-Options, dll.) dikirim oleh aplikasi; jangan menduplikasinya di web server.

## 5. Penjadwal (cron)

Satu entri cron menjalankan semua tugas terjadwal (saat ini: anonimisasi aspirasi pukul 02:00):

```cron
* * * * * www-data cd /var/www/desa && php artisan schedule:run >> /dev/null 2>&1
```

Periksa: `php artisan schedule:list` dan `php artisan aspirasi:anonimkan --hitung`.

## 6. Backup & pemulihan (NFR-07)

**Yang harus dicadangkan setiap hari:** database MySQL dan folder `storage/app` (gambar publik di `storage/app/public`, PDF privat di `storage/app/private`), ditambah `.env`/`APP_KEY` (disimpan terpisah dan aman).

```bash
#!/bin/bash   # /usr/local/bin/backup-desa.sh  (cron harian 01:30)
set -euo pipefail
TGL=$(date +%F); DIR=/var/backups/desa/$TGL; mkdir -p "$DIR"
mysqldump --single-transaction --default-character-set=utf8mb4 -u BACKUP_USER -p"$BACKUP_PASS" siprodes | gzip > "$DIR/db.sql.gz"
tar -czf "$DIR/storage.tar.gz" -C /var/www/desa storage/app
find /var/backups/desa -mindepth 1 -maxdepth 1 -mtime +30 -exec rm -rf {} +   # simpan 30 hari
# lalu salin /var/backups/desa ke lokasi LUAR server (mis. rclone/rsync ke penyimpanan lain)
```

**Pemulihan:**

```bash
php artisan down
gunzip < db.sql.gz | mysql -u USER -p siprodes
tar -xzf storage.tar.gz -C /var/www/desa
php artisan storage:link && php artisan config:cache && php artisan up
```

Uji pemulihan ke server percobaan minimal sekali sebelum rilis dan setiap beberapa bulan.

## 7. Pembaruan aplikasi

```bash
php artisan down
git pull
composer install --no-dev --optimize-autoloader
npm ci && npm run build        # atau unggah public/build
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan event:cache
php artisan up
```

Rollback: kembalikan commit sebelumnya, jalankan `composer install`, dan pulihkan database dari cadangan bila ada migrasi yang tidak dapat dibalik.

## 8. Pemantauan

- Cek kesehatan: `GET /up` (HTTP 200).
- Log aplikasi: `storage/logs/laravel.log` (putar log dengan logrotate). Galat 500 tampil sebagai halaman desa tanpa jejak teknis.
- Audit log tindakan admin: menu *Akun & Hak Akses → Log Aktivitas*.

## 9. Daftar periksa sebelum membuka ke publik

- [ ] `APP_ENV=production`, `APP_DEBUG=false`, HTTPS aktif, HTTP dialihkan ke HTTPS.
- [ ] Super Admin pertama dibuat dan kata sandi awal sudah diganti.
- [ ] Email atur ulang kata sandi sampai (SMTP diuji).
- [ ] Data contoh tidak ada di produksi (`contoh = true` tidak ada); seluruh konten sudah data resmi desa.
- [ ] Kontak/alamat/peta baru diterbitkan setelah diverifikasi desa.
- [ ] Backup harian berjalan dan pemulihan sudah diuji.
- [ ] Cron `schedule:run` aktif.
- [ ] Header keamanan diperiksa (securityheaders.com) dan konsol browser bebas galat CSP.
- [ ] Uji unggah gambar dan PDF dari HP; batas PHP sesuai bagian 1.
