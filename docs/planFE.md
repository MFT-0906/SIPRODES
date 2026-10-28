# planFE.md — Rencana Frontend Website Desa Marga Agung

> Rencana kerja bertahap untuk Claude Code. Acuan utama: `docs/DESIGN.md`, SKPL di `docs/`, dan file Figma **Website Desa Marga Agung — UI Design V2**.
> Kerjakan **satu fase per sesi**. Setiap fase diakhiri titik periksa (checkpoint) untuk ditinjau manusia sebelum lanjut.

---

## Cara pakai (cukup satu perintah, diulang tiap fase)

Ketik perintah ini di Claude Code setiap kali ingin melanjutkan:

```text
Baca docs/planFE.md dan kerjakan fase berikutnya yang statusnya belum "Selesai".
Ikuti bagian "Aturan kerja". Setelah fase selesai: centang tugasnya, isi Log,
ubah status fase di tabel, commit, lalu berhenti dan laporkan hasilnya.
```

Jika hasil fase belum sesuai, tulis koreksinya, lalu minta: `Perbaiki fase N sesuai catatan, lalu laporkan lagi.`

---

## Status fase

| Fase | Nama | Status |
|---|---|---|
| 0 | Persiapan proyek & aturan | Selesai |
| 1 | Design token & gaya dasar | Selesai |
| 2 | Komponen dasar (UI kit) | Selesai |
| 3 | Layout publik & navigasi | Selesai |
| 4 | Halaman publik A: Beranda, Profil | Selesai |
| 5 | Halaman publik B: APBDes, Potensi | Belum |
| 6 | Halaman publik C: Berita, Detail, Aspirasi, Kontak | Belum |
| 7 | Halaman status: 404, kosong, memuat, gagal | Belum |
| 8 | Layout admin, Login, Lupa kata sandi | Belum |
| 9 | Admin A: Ringkasan, Berita, Agenda | Belum |
| 10 | Admin B: Profil, Struktur, APBDes, Pertanian, Kelompok Tani, UMKM, Aspirasi, Akun | Belum |
| 11 | QA: responsif, aksesibilitas, performa & laporan | Belum |

Ubah kolom Status menjadi `Sedang` saat mulai dan `Selesai` setelah checkpoint lolos.

---

## Aturan kerja (wajib di setiap fase)

1. **Baca dulu** `docs/DESIGN.md`, SKPL, dan bagian fase yang dikerjakan. Periksa struktur repository sebelum mengubah apa pun; jangan mengganti stack tanpa alasan.
2. **Stack:** PHP 8.x + Laravel (versi yang masih mendapat dukungan keamanan), Blade, Vite, **Tailwind CSS saja** (jangan dicampur Bootstrap), JavaScript seminimal mungkin (Alpine.js boleh untuk menu, tab, dan dialog).
3. **Figma lewat MCP:** untuk setiap layar, ambil konteks desain dari link node yang tercantum. Baca juga **anotasi** pada frame; anotasi berisi aturan data (mis. kontak tampil setelah diverifikasi).
4. **Jangan mengarang data.** Data sementara diletakkan di `app/Support/DemoContent.php` (atau file serupa), diberi komentar `// TODO(backend): ganti dengan data dari database`. Jangan menulis data langsung di template Blade.
5. **Tanpa foto AI atau stok.** Pakai komponen placeholder gambar sampai foto resmi tersedia. Setiap `<img>` wajib punya `alt`.
6. **Bahasa Indonesia baku** untuk semua teks antarmuka.
7. **Aksesibilitas minimum:** label form terhubung ke input, fokus keyboard terlihat, target sentuh ≥ 44 × 44 px, status tidak hanya dibedakan warna, kontras teks ≥ 4,5:1 (token sudah lolos, jangan diganti asal).
8. **Mobile-first.** Bangun dari 390 px ke atas; breakpoint mengikuti DESIGN.md §8.
9. **Jangan membuat kontrol palsu.** Tombol, filter, dan pagination yang belum tersambung backend diberi `// TODO(backend)` dan tetap berfungsi secara tampilan (tidak error).
10. **Keamanan tetap di backend.** Frontend hanya validasi untuk kenyamanan; jangan menaruh logika izin atau rahasia di JavaScript.
11. **Selesai fase:** jalankan `npm run build` tanpa error, buka halaman terkait di browser (`php artisan serve`), bandingkan dengan frame Figma, perbaiki selisih yang mencolok, lalu commit: `feat(fe): fase N — <ringkasan>`.

---

## Referensi Figma

File V2: `https://www.figma.com/design/ajsIaMebhpIfIRfdkqHFDo`
Format link node: `https://www.figma.com/design/ajsIaMebhpIfIRfdkqHFDo?node-id=<ID dengan tanda ->`
Contoh: node `8:75` → `?node-id=8-75`.

### Design System (halaman `0:1`)

| Komponen | Node | Komponen | Node |
|---|---|---|---|
| Fondasi warna & tipografi | `20:2850` | Ikon | `5:2` |
| Button | `5:213` | Badge | `5:235` |
| Form Field (input/textarea) | `5:292` | Select | `48:206` |
| Checkbox | `48:144` | Radio | `48:151` |
| Toggle | `48:162` | Uploader | `48:259` |
| Search Field | `72:174` | Filter Dropdown | `70:172` |
| Chip | `72:184` | Pagination | `48:260` |
| Breadcrumb | `48:275` | Placeholder gambar | `6:8` |
| Card Berita | `6:25` | Card UMKM | `6:46` |
| Card Agenda | `6:63` | Card Akses Cepat | `6:81` |
| Card Statistik | `6:89` | Card Perangkat | `13:447` |
| State Kosong | `48:281` | Skeleton Kartu | `48:296` |
| Logo Desa (terang/gelap) | `2006:239` | Strip Wilayah | `2006:272` |
| Header Publik Desktop | `6:93` | Header Publik Mobile | `6:132` |
| Footer Publik | `6:147` | Admin Sidebar | `17:78` |
| Admin Topbar | `17:188` | | |

### Publik — Desktop (halaman `1:2`) dan Mobile/Tablet (halaman `1:3`)

| Layar | Desktop | Mobile / Tablet |
|---|---|---|
| Beranda | `8:75` | `15:79` (HP), `26:459` (tablet) |
| Menu navigasi HP | — | `15:304` |
| Profil Desa | `13:456` | `52:680` |
| APBDes | `11:352` | `15:384` |
| Potensi Desa | `13:712` | `52:795` |
| Berita & Agenda | `14:762` | `52:882` |
| Detail Berita | `14:1037` | `52:1003` |
| Ruang Aspirasi | `14:1219` | `15:490` |
| Aspirasi: state error / berhasil / mengirim | `14:1380` | — |
| Kontak & Lokasi | `23:1317` | `23:2978` |
| Berita kosong | `50:1460` | — |
| Memuat (skeleton) | `50:1574` | — |
| 404 | `50:1704` | — |
| Gagal memuat | `50:1795` | — |

### Admin (halaman `1:4`)

| Layar | Node | Layar | Node |
|---|---|---|---|
| Login | `17:209` | Lupa kata sandi | `58:3955` |
| Ringkasan | `17:267` | Kelola Berita | `18:362` |
| Form Tambah Berita | `18:714` | Konfirmasi Hapus | `18:966` |
| Berita — tabel kosong | `50:4848` | Kelola Agenda | `24:1944` |
| Profil (Visi & Misi) | `24:1330` | Profil (Sejarah) | `58:3366` |
| Profil (Lokasi) | `58:3556` | Struktur Perangkat | `24:1554` |
| Kelola APBDes | `19:1224` | APBDes — unggah ditolak | `50:5479` |
| Potensi Pertanian | `25:1983` | Kelompok Tani | `58:3099` |
| Kelola UMKM | `25:2305` | Aspirasi Warga | `19:962` |
| Aspirasi — kosong | `50:5210` | Akun & Hak Akses | `25:2645` |
| Tambah Akun (modal) | `58:3772` | Admin HP: daftar aspirasi | `26:4407` |
| Admin HP: menu samping | `26:4471` | | |

---

## Fase 0 — Persiapan proyek & aturan

**Tujuan:** proyek siap dikerjakan dengan struktur yang konsisten.

- [x] Periksa repository: versi PHP/Laravel, apakah Vite dan Tailwind sudah terpasang. Laporkan temuan sebelum mengubah.
- [x] Pasang/konfigurasi Tailwind melalui Vite bila belum ada (tanpa Bootstrap).
- [x] Muat font **Plus Jakarta Sans** (judul: 600, 700) dan **Inter** (isi: 400, 500, 600) dengan `font-display: swap`.
- [x] Buat struktur folder:
  - `resources/views/layouts/` — `public.blade.php`, `admin.blade.php`, `auth.blade.php`
  - `resources/views/components/ui/` — komponen dasar
  - `resources/views/components/public/` dan `components/admin/` — komponen khusus area
  - `resources/views/pages/public/` dan `pages/admin/`
  - `app/Support/DemoContent.php` — data sementara
- [x] Tambahkan ke `CLAUDE.md`: ringkasan aturan kerja di atas dan link file Figma V2.
- [x] Pastikan koneksi Figma MCP berfungsi (ambil konteks node `20:2850`).

**Checkpoint:** `npm run build` berhasil, halaman default Laravel tampil, laporan versi stack.

---

## Fase 1 — Design token & gaya dasar

**Sumber Figma:** `20:2850` (fondasi), variabel lokal file V2.

- [x] Petakan token ke tema Tailwind (pakai `@theme` di CSS bila Tailwind v4, atau `tailwind.config.js` bila v3). Nama mengikuti token Figma, mis. `bg-primary`, `text-muted`, `border-border`.

| Token | Nilai | Token | Nilai |
|---|---|---|---|
| primary | `#1B5E20` | primary-dark | `#14491A` |
| primary-soft | `#EDF3E6` | accent | `#F9A825` |
| accent-dark | `#E0941A` | accent-soft | `#FDF3DC` |
| text | `#212121` | muted | `#53594F` |
| border | `#D5DBD1` | surface | `#FFFFFF` |
| background | `#FAF7EE` | success | `#2E7D32` |
| success-soft | `#E8F5EE` | warning | `#8A5A12` |
| warning-soft | `#FDF4E3` | danger | `#C62828` |
| danger-soft | `#FDECEA` | danger-dark | `#9F1F1F` |
| info | `#2D5BA8` | info-soft | `#EAF0FA` |

- Radius: input/tombol `8px`, kartu `16px`, pill `999px`. Jarak: kelipatan 4 (4, 8, 12, 16, 24, 32, 48, 64).
- [x] Buat kelas tipografi (atau komponen teks) sesuai gaya teks Figma:

| Gaya | Font | Ukuran / tinggi baris |
|---|---|---|
| Display (hero desktop) | Plus Jakarta Sans 700 | 36 / 1.2 |
| H1 | Plus Jakarta Sans 700 | 32 / 1.25 (HP: 28 / 1.25) |
| H2 | Plus Jakarta Sans 700 | 24 / 1.3 |
| H3 | Plus Jakarta Sans 600 | 18 / 1.4 |
| Body Large | Inter 400 | 18 / 1.6 |
| Body | Inter 400 | 16 / 1.6 |
| Body Small | Inter 400 | 14 / 1.5 |
| Label | Inter 600 | 14 / 1.4 |
| Label Small | Inter 500 | 12 / 1.4 |

- [x] Gaya dasar: latar halaman `background`, teks `text`, cincin fokus global (`outline` 3px warna primary dengan offset), hormati `prefers-reduced-motion`.
- [x] Wadah konten: maks. 1200 px, padding samping 16 px (HP) dan 24 px (≥ 1024 px).

**Checkpoint:** halaman uji sederhana menampilkan semua warna dan gaya teks; cocokkan dengan node `20:2850`.

---

## Fase 2 — Komponen dasar (UI kit)

**Sumber Figma:** tabel Design System di atas. Ambil konteks tiap komponen dari node-nya.

- [x] `x-ui.button` — type: `primary | accent | secondary | ghost | danger`; size: `m | s`; state: disabled & loading (spinner + `aria-busy`, cegah klik ganda); ikon opsional. Bisa berupa `<button>` atau `<a>`.
- [x] `x-ui.icon` — set ikon garis 24 px (Lucide atau SVG dari Figma `5:2`), warna mengikuti `currentColor`.
- [x] `x-ui.badge` — nada: `sukses | netral | info | peringatan | bahaya | utama | aksen`; selalu berteks.
- [x] Form: `x-ui.field` (input/textarea dengan label, penanda Wajib/Opsional, teks bantuan, pesan error + `aria-describedby`), `x-ui.select`, `x-ui.checkbox`, `x-ui.radio`, `x-ui.toggle`, `x-ui.uploader` (kosong, mengunggah, berhasil, ditolak).
- [x] Navigasi & daftar: `x-ui.breadcrumb`, `x-ui.pagination`, `x-ui.search`, `x-ui.filter-dropdown`, `x-ui.chip`.
- [x] Kartu: `x-ui.card-news`, `x-ui.card-umkm` (tombol WhatsApp hanya jika `whatsappApproved`), `x-ui.card-agenda` (lokasi opsional), `x-ui.card-quick`, `x-ui.card-stat` (wajib ada sumber), `x-ui.card-official`.
- [x] Status: `x-ui.empty-state`, `x-ui.skeleton-card`, `x-ui.alert` (info/sukses/peringatan/bahaya), `x-ui.toast`, `x-ui.modal` (fokus terkunci di dalam, tutup dengan Esc).
- [x] `x-ui.image-placeholder` — kotak foto + ikon + keterangan singkat.
- [x] Halaman katalog `/_komponen` (hanya aktif di `APP_ENV=local`) yang menampilkan semua komponen dan variannya.

**Checkpoint:** katalog `/_komponen` tampil; setiap komponen bisa dioperasikan dengan keyboard; bandingkan dengan halaman Design System Figma.

---

## Fase 3 — Layout publik & navigasi

**Sumber Figma:** `6:93`, `6:132`, `15:304`, `2006:239`, `2006:272`, `6:147`.

- [x] `layouts/public.blade.php`: skip link "Lewati ke konten", header, strip wilayah, `<main>`, footer.
- [x] Header desktop (≥ 1024 px): logo (varian gelap), menu Beranda · Profil Desa · APBDes · Potensi Desa · Berita & Agenda · Kontak, tombol aksen "Ruang Aspirasi". Menu aktif ditandai garis kuning + `aria-current="page"`.
- [x] Header HP: logo + tombol menu 44 px; menu layar penuh sesuai `15:304`, bisa ditutup dengan tombol × dan Esc, fokus kembali ke tombol menu.
- [x] Footer: identitas, kontak resmi (tampil hanya jika data kontak `verified`), tautan penting, hak cipta.
- [x] Rute publik bernama: `home`, `profil`, `apbdes`, `potensi`, `berita.index`, `berita.show`, `aspirasi.create`, `kontak`. Isi sementara: halaman kosong dengan judul.
- [x] APBDes dan Berita harus bisa dicapai maksimal dua klik dari Beranda.

**Checkpoint:** navigasi berfungsi di 390 px, 768 px, dan 1440 px; menu HP bisa dipakai dengan keyboard.

---

## Fase 4 — Halaman publik A: Beranda, Profil

**Sumber Figma:** Beranda `8:75`, `15:79`, `26:459` · Profil `13:456`, `52:680`.

- [x] Data sementara di `DemoContent`: statistik desa (±4.492 jiwa, 1.309 KK, 1.050 ha, 6 dusun — sumber "survei 2026", tandai perlu verifikasi), 2–3 berita contoh, 3 agenda contoh, 2 UMKM contoh.
- [x] **Beranda:** hero (judul, deskripsi, tombol "Lihat APBDes" primary, "Sampaikan aspirasi" accent, "Jelajahi profil desa" ghost, foto placeholder) → data singkat desa → layanan informasi (5 kartu) → potensi unggulan (sorotan jagung + 2 UMKM) → berita terbaru + agenda desa → ajakan aspirasi → footer.
- [x] **Profil:** navigasi bagian (anchor + `scroll-margin`), sejarah + fakta singkat, visi & misi, struktur pemerintahan (grid kartu perangkat), lokasi (alamat teks + area peta; alamat tetap tampil bila peta gagal).
- [x] Hormati anotasi Figma: konten yang belum resmi tidak ditampilkan sebagai fakta.

**Checkpoint:** kedua halaman cocok dengan Figma di HP, tablet, dan desktop; tidak ada data yang ditulis langsung di template.

---

## Fase 5 — Halaman publik B: APBDes, Potensi

**Sumber Figma:** APBDes `11:352`, `15:384` · Potensi `13:712`, `52:795`.

- [ ] **APBDes:** pemilih tahun (tab di desktop, select di HP), ringkasan (Total pendapatan, Total belanja, Pembiayaan netto) dengan format `Rp` Indonesia, tabel pendapatan & belanja (di HP berubah menjadi kartu), area diagram pendamping (tabel tetap acuan utama), "Apa arti angka ini?", arsip PDF + tombol "Unduh PDF", empty state jika tahun belum punya dokumen. Nilai sementara: `Rp —`.
- [ ] **Potensi:** tab Pertanian/UMKM (tab yang bisa diakses keyboard, `role="tablist"`), pencarian, kartu komoditas (musim panen opsional), tabel kelompok tani (kontak hanya jika disetujui), katalog UMKM + pagination (target ≥ 10 UMKM), ajakan "Ingin usaha Anda tercantum di sini?".
- [ ] Helper format: `formatRupiah()` dan format tanggal Indonesia (`18 Okt 2026`).

**Checkpoint:** tabel APBDes terbaca tanpa diagram; tab dan pencarian berfungsi di HP.

---

## Fase 6 — Halaman publik C: Berita, Detail, Aspirasi, Kontak

**Sumber Figma:** Berita `14:762`, `52:882` · Detail `14:1037`, `52:1003` · Aspirasi `14:1219`, `15:490`, state `14:1380` · Kontak `23:1317`, `23:2978`.

- [ ] **Berita:** chip kategori, pencarian, grid kartu, pagination / "Muat berita lainnya" di HP, kolom agenda desa (agenda lewat bisa diciutkan).
- [ ] **Detail berita:** breadcrumb, kategori, tanggal, judul, gambar utama + keterangan, isi artikel (lebar baca ± 70 karakter), kutipan, tombol kembali & bagikan, berita lainnya. Hanya berita berstatus terbit yang ditampilkan.
- [ ] **Ruang Aspirasi:** alur penanganan (3 langkah), catatan privasi, tips "Agar mudah dipahami", formulir Nama (wajib), NIK (opsional, 16 digit), Isi pesan (wajib, min. 20 karakter, penghitung karakter), peringatan data sensitif, tombol kirim dengan state loading. Validasi sisi klien + ringkasan error di atas form; state berhasil tanpa membocorkan data. Form mengirim ke rute `aspirasi.store` (`// TODO(backend)`), sertakan `@csrf`.
- [ ] **Kontak:** kartu telepon/email/aspirasi dengan tautan `tel:`/`mailto:` (hanya jika data `verified`), alamat, jam layanan, peta + tombol Google Maps.

**Checkpoint:** semua state form aspirasi (kosong, error, mengirim, berhasil) bisa dicoba; form bisa diisi penuh dengan keyboard.

---

## Fase 7 — Halaman status

**Sumber Figma:** `50:1460`, `50:1574`, `50:1704`, `50:1795`.

- [ ] Halaman `resources/views/errors/404.blade.php` sesuai Figma (dan 500 dengan gaya yang sama, tanpa detail teknis).
- [ ] Empty state berita (belum ada berita / hasil pencarian kosong) dipakai otomatis saat daftar kosong.
- [ ] Skeleton saat memuat (dipakai bila ada pemuatan bertahap) dan state gagal memuat dengan tombol "Coba lagi".

**Checkpoint:** setiap state bisa dipicu lewat data contoh (mis. parameter `?demo=kosong` hanya di lokal).

---

## Fase 8 — Layout admin, Login, Lupa kata sandi

**Sumber Figma:** `17:78`, `17:188`, `17:209`, `58:3955`, `26:4471`.

- [ ] `layouts/admin.blade.php`: sidebar (grup Utama, Konten Desa, Potensi, Publikasi, Layanan, Administrasi), topbar (breadcrumb, judul halaman, "Lihat situs publik", menu akun), area konten. Di HP, sidebar menjadi menu samping yang bisa dibuka/tutup.
- [ ] Menu "Akun & Hak Akses" hanya ditampilkan jika pengguna berperan Super Admin (dari variabel `$user->role` — **izin tetap dicek backend**).
- [ ] Badge jumlah aspirasi baru di menu.
- [ ] Halaman Login (email/username + kata sandi, ingat saya, pesan gagal umum) dan Lupa kata sandi (pesan konfirmasi sama untuk email terdaftar/tidak terdaftar).
- [ ] Rute admin dengan prefix `admin.` (mis. `admin.dashboard`, `admin.berita.index`) — middleware auth dipasang oleh backend (`// TODO(backend)`).

**Checkpoint:** navigasi admin berfungsi di desktop dan HP; menu menyesuaikan peran Staf vs Super Admin.

---

## Fase 9 — Admin A: Ringkasan, Berita, Agenda

**Sumber Figma:** `17:267`, `18:362`, `18:714`, `18:966`, `50:4848`, `24:1944`.

- [ ] **Ringkasan:** kartu statistik (dari data, bukan angka statis), aspirasi terbaru, pintasan, konten draf terakhir.
- [ ] **Kelola Berita:** pola standar (judul + aksi utama → pencarian/filter → tabel → aksi per baris → pagination), status Draf/Terbit, toast "Perubahan berhasil disimpan.", tabel kosong.
- [ ] **Form berita:** judul, ringkasan, editor isi, status Draf/Terbit, tanggal, kategori, gambar utama (uploader) + alt text wajib, tombol Batal / Simpan Draf / Simpan & Terbitkan.
- [ ] **Dialog hapus:** menyebut nama item, tombol Danger, fokus terkunci.
- [ ] **Kelola Agenda:** tabel + form (judul, tanggal, waktu opsional, lokasi opsional, status, deskripsi).

**Checkpoint:** alur tambah → simpan → muncul di tabel → hapus dengan konfirmasi bisa dicoba dengan data demo.

---

## Fase 10 — Admin B: modul lainnya

**Sumber Figma:** Profil `24:1330`, `58:3366`, `58:3556` · Struktur `24:1554` · APBDes `19:1224`, `50:5479` · Pertanian `25:1983` · Kelompok Tani `58:3099` · UMKM `25:2305` · Aspirasi `19:962`, `50:5210`, `26:4407` · Akun `25:2645`, `58:3772`.

- [ ] **Profil:** tab Sejarah, Visi & Misi (butir misi bisa ditambah/diurutkan), Lokasi & Geografis (alamat, koordinat, kontak + centang "sudah diverifikasi"), Data ringkas.
- [ ] **Struktur perangkat:** daftar berurutan (urutan bisa diubah), form foto/nama/jabatan, toggle tampil di publik.
- [ ] **APBDes:** daftar periode, form ringkasan (validasi angka), unggah PDF (state ditolak & berhasil), toast gagal simpan.
- [ ] **Pertanian & Kelompok Tani:** tabel + form; kontak kelompok tani dengan centang persetujuan publikasi.
- [ ] **UMKM:** tabel + form; WhatsApp dan foto hanya tampil di publik jika centang persetujuan pemilik aktif.
- [ ] **Aspirasi:** filter status (Baru/Sudah Dibaca/Selesai), daftar + detail, NIK disamarkan, ubah status, catatan tindak lanjut, riwayat; tampilan HP `26:4407`; state kosong.
- [ ] **Akun & Hak Akses:** tabel akun, matriks izin (draf), modal Tambah Akun (tanpa mengisi kata sandi; pengguna menerima tautan).

**Checkpoint:** seluruh menu admin membuka halaman yang sesuai Figma; tidak ada tombol yang menimbulkan error.

---

## Fase 11 — QA & laporan

- [ ] Cek responsif di 360, 390, 768, 1024, dan 1440 px; tidak ada gulir horizontal (kecuali tabel dengan petunjuk gulir).
- [ ] Aksesibilitas: navigasi keyboard semua halaman, fokus terlihat, label form, alt text, kontras (cek otomatis dengan axe/Lighthouse bila tersedia).
- [ ] Performa: gambar `loading="lazy"` dan ukuran sesuai, CSS/JS minimal, Lighthouse halaman Beranda (catat kondisi uji; target muat < 3 detik sesuai SKPL NFR-01).
- [ ] Pastikan konten draf tidak tampil di publik dan aspirasi tidak pernah tampil di halaman publik.
- [ ] Buat `docs/laporanFE.md`: halaman yang selesai, kebutuhan SKPL yang belum terpenuhi, daftar `TODO(backend)`, keputusan yang perlu dikonfirmasi desa, dan hasil uji responsif (DESIGN.md §15 poin 10).

**Checkpoint:** laporan ditinjau tim; semua fase berstatus `Selesai`.

---

## Log

| Tanggal | Fase | Ringkasan | Commit |
|---|---|---|---|
| 2026-10-28 | 0 | Stack: PHP 8.3 (Composer)/8.5 (CLI), Laravel 13.38, Vite 8, Tailwind 4 (tanpa Bootstrap). Font Plus Jakarta Sans + Inter di-host lokal lewat plugin fonts Vite (`display: swap`, variabel `--font-heading`/`--font-body`). Struktur folder, `DemoContent.php`, aturan di `CLAUDE.md`. Figma MCP terverifikasi (node `20:2850`, token cocok). Catatan: Figma memberi letter-spacing −0.4 px pada Display/H1/H2 (dipakai di fase 1); `DESIGN.md` memakai palet lama, ikuti token Figma. | `4e862ed` |
| 2026-10-28 | 1 | Token warna, radius, dan tipografi di `@theme` + `@utility type-*` (`resources/css/app.css`); palet bawaan Tailwind dikosongkan. Gaya dasar, cincin fokus (kuning di `.on-dark`), reduced-motion, `container-page`. Halaman uji lokal `/_tokens`; nilai komputasi font/ukuran/bobot/line-height/letter-spacing cocok dengan Figma `20:2850` di 1280 px dan 390 px. Catatan: `accent-dark` dan `danger-dark` hanya dari plan (bukan variabel Figma di node itu). | `5682d4f` |
| 2026-10-28 | 2 | 26 komponen `x-ui.*` di `resources/views/components/ui/` (button, icon, badge, field, select, checkbox, radio, toggle, uploader, breadcrumb, pagination, search, filter-dropdown, chip, 6 kartu, empty-state, skeleton-card, alert, toast, modal, image-placeholder). Ikon: 37 SVG Figma dirakit ke kanvas 24 px (`resources/svg/icons`, `currentColor`). Alpine.js + plugin focus untuk modal/toast/dropdown/uploader (`resources/js/app.js`). Katalog lokal `/_komponen`; diuji di 1280/390/360 px (tanpa gulir horizontal): dialog (fokus terkunci, Esc, fokus kembali), toast, filter, uploader, kunci tombol kirim. Catatan: prop varian tombol bernama `variant` (bukan `type`, bentrok dengan atribut HTML); `<select>` memakai elemen asli sehingga daftar opsinya mengikuti gaya peramban; tombol ukuran S setinggi 36 px sesuai Figma dengan area sentuh 44 px; batas ukuran unggahan belum ditetapkan desa (`TODO(backend)`). | `46e87cd` |
| 2026-10-28 | 3 | `layouts/public.blade.php` (skip link, header, strip wilayah, `<main id="konten">`, footer, toast) dan `x-public.{logo,header,region-strip,footer}`. Header desktop ≥1024 px (menu aktif: garis kuning + `aria-current`, CTA aksen); HP/tablet: tombol menu 44 px + menu layar penuh (fokus terkunci, Esc/× menutup, fokus kembali). Footer: kontak resmi hanya tampil bila `verified` (saat ini semua belum, jadi kolom Kontak Resmi disembunyikan, berbeda dari Figma yang memuat placeholder). 8 rute bernama (`home`, `profil`, `apbdes`, `potensi`, `berita.index`, `berita.show`, `aspirasi.create`, `kontak`) berisi halaman kerangka berjudul. `welcome.blade.php` bawaan dihapus. Diuji 1440/1024/768/390 px: tanpa gulir horizontal, APBDes dan Berita 1 klik dari Beranda. Catatan: penanda aktif menu desktop diposisikan absolut sehingga teks semua menu sejajar (di Figma teks menu aktif sedikit lebih tinggi); catatan "Menu dapat ditutup dengan tombol ×…" di Figma dianggap catatan desain dan tidak ditampilkan. | `d5e8c98` |
| 2026-10-28 | 4 | Beranda (hero, data singkat, layanan informasi, potensi unggulan, berita terbaru + agenda, ajakan aspirasi) dan Profil Desa (judul, navigasi bagian dengan scrollspy, sejarah + fakta singkat, visi-misi, struktur pemerintahan, lokasi). Data lewat `PublicPageController` → `DemoContent::{beranda,profil,statistik}` (tidak ada data di Blade). Angka penduduk/KK/luas/dusun diberi sumber "survei awal 2026 · perlu verifikasi desa". Visi-misi dan nama perangkat bertanda "Menunggu naskah/data resmi desa". Alamat kantor, kontak, dan tombol Google Maps disembunyikan sampai diverifikasi (peta tampil sebagai placeholder). Tombol WhatsApp UMKM tidak tampil (belum disetujui). Diuji di 360/390/768/900/1024/1100/1280/1440 px tanpa gulir horizontal (sempat ditemukan dan diperbaiki overflow hero di 1024 px). Perubahan lain: `APP_NAME` jadi "Desa Marga Agung" (`.env`, `.env.example`); `x-ui.card-quick` punya mode `row`; `x-ui.chip` mendukung `aria-current` dari klien. Perbedaan dari Figma: tombol ghost "Jelajahi profil desa" hanya di ≥1024 px (Figma HP/tablet hanya 2 tombol); baris "Alamat kantor" dan "Batas wilayah" di Lokasi tidak ditampilkan (datanya belum ada). | — |
