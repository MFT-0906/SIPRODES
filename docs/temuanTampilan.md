# Temuan Tampilan — Seluruh Halaman Publik

Tanggal pemeriksaan: 4 November 2026.
Cakupan: Beranda, Profil Desa, Transparansi APBDes, Potensi Desa (kedua tab), Berita & Agenda, detail Berita, Kontak & Lokasi, Ruang Aspirasi (awal, galat, berhasil), halaman 404 dan 429, menu HP, navigasi keyboard, halaman masuk admin, dan 14 halaman dashboard admin (sebagai Super Admin). Yang belum diperiksa tercatat di akhir dokumen.
Cara: halaman dijalankan di `http://127.0.0.1:8000`, difoto dengan Playwright pada lebar 1440 px (desktop) dan 390 px (HP), dinilai dengan acuan Web Interface Guidelines dan taste-skill, lalu diperiksa otomatis (kontras, target sentuh, label, alt, heading, luapan horizontal, galat konsol) di 11 alamat pada dua lebar layar.
Data: database lokal berisi data contoh dari `DataDummyLengkapSeeder`. Nama perangkat desa dan butir visi-misi pada tangkapan layar adalah data contoh/penampung, bukan data resmi.
Tidak ada kode yang diubah. Konsol browser tidak menunjukkan galat pada halaman yang ada (satu-satunya galat adalah 404 yang memang disengaja pada alamat uji yang tidak ada).

Keterangan: `[ ]` belum dikerjakan, **Tinggi/Sedang/Rendah** = perkiraan dampak.

---

## 1. Profil Desa (`/profil-desa`)

### Sudah baik
- Judul `h1` jelas, breadcrumb ada, tab bagian (Sejarah, Visi dan Misi, Struktur Pemerintahan, Lokasi) mudah dipindai.
- Tabel "Fakta singkat" rapi, angka rata kanan, dan catatan sumber ("menunggu verifikasi desa") jujur sesuai aturan data.
- Peta satelit (Leaflet) berfungsi, ada atribusi, tombol "Buka di Google Maps", dan tautan "Lihat peta lebih besar".
- Di HP, daftar perangkat dipangkas jadi empat kartu dengan tombol "Lihat semua perangkat".

### Temuan
- [x] **Selesai — Visi dan misi masih penampung.** Kartu visi berisi "Visi resmi Desa Marga Agung" dan misi "Butir misi 1–4". Lencana "Menunggu naskah resmi desa" sudah ada, tetapi isi penampungnya terbaca seperti kesalahan. Pertimbangkan tampilan kosong yang jelas ("Naskah visi dan misi belum tersedia") sampai naskah resmi masuk. Perbaikan: `KontenPublik::profil()` tidak lagi mengisi teks penampung, dan `pages/public/profil.blade.php` menampilkan keterangan "Naskah visi dan misi belum tersedia" (atau keterangan per bagian bila salah satunya sudah diisi). Lencana "Menunggu naskah resmi desa" tetap ada.
- [ ] **Tinggi — Foto perangkat berupa ikon orang yang sama.** Tujuh kartu dengan lingkaran ikon identik. Foto asli (dengan izin) akan paling mengubah halaman ini.
- [ ] **Sedang — Nama perangkat adalah data contoh.** "Slamet Riyanto", "Siti Rahayu", dan seterusnya berasal dari seeder. Pastikan diganti nama resmi lewat Data Master sebelum rilis, dan lencana "Nama dan foto menunggu data resmi desa" tetap tampil selama belum diganti.
- [x] **Selesai — Tab di HP terpotong.** Baris tab melebar melewati layar sehingga "Struktur Pemerintahan" terpotong dan "Lokasi" tidak terlihat. Tidak ada petunjuk bahwa bisa digeser. Pertimbangkan bayangan tepi atau tab yang membungkus ke baris kedua. Perbaikan: baris tab membungkus ke baris kedua (`flex-wrap`, tanpa gulir mendatar) di `pages/public/profil.blade.php`; keempat tab selalu terlihat.
- [x] **TIDAK DIUBAH — Grid perangkat tidak penuh.** Tujuh kartu pada empat kolom menyisakan satu sel kosong di baris kedua desktop. Grid yang menyisakan sel kosong bila jumlah kartu bukan kelipatan kolom adalah sifat grid kartu seragam; memperbaikinya (mis. memusatkan baris terakhir) menyimpang dari Figma dan bergantung pada jumlah data nyata.
- [x] **Tidak diubah — Label "VISI" dan "MISI" berukuran kecil dengan huruf kapital.** Taste-skill menghindari label kapital kecil. Dapat dibuat huruf biasa dengan ukuran lebih besar. Label kapital kecil itu ada di desain Figma (node 13:456), jadi dipertahankan.
- [x] **Selesai — Keterangan peta soal "roda mouse".** Tidak relevan di layar sentuh. Sebutkan cara yang sesuai perangkat, atau hilangkan. Perbaikan: keterangan peta kini "Tampilan satelit dengan label jalan. Gunakan tombol + dan − untuk memperbesar peta." (`components/public/peta.blade.php`), berlaku juga di Kontak & Lokasi. Diuji.
- [x] **Selesai — Catatan sumber di "Fakta singkat" mengulang kalimat yang sama** dengan kartu statistik di Beranda. Bisa dibuat satu pola tampilan. Perbaikan: catatan sumber di Profil dan Beranda memakai pola yang sama dengan Figma, "Sumber: …" (tidak digandakan bila teksnya sudah diawali "Sumber"). Diuji.

---

## 2. Kontak & Lokasi (`/kontak-lokasi`)

### Sudah baik
- Susunan jelas: tiga kartu kontak, lalu kartu alamat berdampingan dengan peta. Tombol "Buka di Google Maps" menonjol.
- Aturan data ditegakkan: nomor telepon dan email tidak ditampilkan sebelum diverifikasi, dengan penjelasan yang sopan.
- Di HP, urutan menjadi satu kolom tanpa luapan horizontal, peta tetap besar.

### Temuan
- [x] **Selesai — Dua dari tiga kartu kontak tidak berisi apa pun.** Telepon dan Email hanya berisi "akan ditampilkan setelah diverifikasi". Pengunjung datang untuk kontak dan tidak menemukannya. Pertimbangkan menggabungkan keduanya menjadi satu kartu ringkas, atau memindahkan jalur yang pasti tersedia (Formulir daring, alamat kantor) ke posisi paling menonjol. Perbaikan: kartu telepon dan email hanya tampil bila sudah diverifikasi; bila keduanya belum, digabung menjadi satu kartu "Telepon dan email resmi" yang mengarahkan ke formulir daring (`pages/public/kontak.blade.php`). Tidak ada data kontak yang dikarang.
- [ ] **Tinggi — Jam layanan juga belum ada.** (BELUM: menunggu data atau keputusan desa; tidak ada nilai bawaan yang dikarang.) Kartu "Kantor desa" ikut menampilkan pesan "akan ditampilkan setelah diverifikasi". Nilai bawaan dapat dicatat di `config/desa.php` jika desa belum memutuskan.
- [x] **Selesai — Kartu "Kantor desa" lebih tinggi daripada isinya.** Ada ruang kosong di bawah tombol karena kartu mengikuti tinggi peta. Isi kartu bisa diratakan ke atas dengan jarak lebih rapat, atau tinggi peta dikurangi. Perbaikan: tombol "Buka di Google Maps" didorong ke dasar kartu (`mt-auto`) sehingga ruang kosong berpindah ke antara isi dan tombol, bukan di bawah tombol.
- [x] **Tidak diubah — Ikon di kotak berwarna pada tiga kartu kontak.** Pola "kartu berikon" yang oleh taste-skill dianggap ciri halaman buatan AI. Pertimbangkan tipografi dan ukuran saja. Kartu berikon itu dibangun dari desain Figma Kontak (node 23:1317, lihat catatan Fase 6 di `docs/planFE.md`) dan senada dengan kartu "Layanan informasi" di Beranda. Mengubahnya berarti menyimpang dari Figma; ikon juga membantu pengunjung memindai saluran kontak.
- [x] **Selesai — Teks bantu peta ("Klik peta lalu gunakan roda mouse")** tidak cocok untuk HP, dan menyatu dengan tautan "Lihat peta lebih besar" dalam satu baris yang padat di desktop. Sudah diperbaiki bersama halaman Profil Desa: keterangan peta kini menyebut tombol + dan −.
- [x] **Tidak diubah — Tautan "Lihat peta lebih besar" dan "Buka di Google Maps"** melakukan hal serupa. Cukup satu yang menonjol. Sengaja dipertahankan: tautan "Lihat peta lebih besar" membuka OpenStreetMap sebagai alternatif non-Google dan diperiksa oleh tes di `tests/Feature/Fase4PublikDatabaseTest.php`. Menghapusnya berarti mengubah keputusan fitur peta sebelumnya.

---

## 3. Ruang Aspirasi (`/ruang-aspirasi`)

### Sudah baik
- Formulir paling matang dari ketiga halaman: label terhubung, penanda "Wajib" dan "Opsional", teks bantu di bawah tiap kolom, penghitung karakter (0/1000), pesan peringatan data sensitif, dan tombol kirim selebar kolom.
- Penjelasan alur tiga langkah dan kotak privasi menjawab kekhawatiran warga sebelum mengisi.
- Di HP, formulir muncul lebih dulu dan penjelasan privasi ringkas muncul di atasnya, lalu penjelasan lengkap di bawah. Urutan ini tepat.
- Kolom jebakan spam disembunyikan dengan benar (tidak terlihat dan tidak bisa difokus).

### Temuan
- [x] **DIKOREKSI — Warna teks petunjuk (placeholder) tampak pucat.** "Tulis nama Anda" dan "Tuliskan pertanyaan…" berwarna abu terang. Ukur kontrasnya terhadap 4,5:1, dan jangan jadikan placeholder satu-satunya petunjuk (teks bantu sudah ada, itu baik). Warna petunjuk isian memakai token `muted` (#53594f), kontrasnya sekitar 7:1 terhadap putih. Bukan temuan; tidak diubah.
- [x] **Selesai — Peringatan privasi tampil dua kali di HP.** Kotak biru "Privasi Anda" di atas formulir, lalu kotak hijau "Privasi Anda" yang lengkap di bawah, ditambah kotak kuning peringatan data sensitif di dalam formulir. Tiga kotak berwarna dengan pesan bertumpang tindih membuat formulir terasa padat. Perbaikan: catatan privasi ringkas di atas formulir (HP) kini berupa kotak hijau berikon perisai tanpa judul ganda, persis seperti Figma node 15:490, bukan kotak biru. Warna di HP jadi hijau (privasi) dan kuning (peringatan data sensitif), bukan tiga warna. Kotak "Privasi Anda" lengkap di bawah formulir dipertahankan karena memuat masa simpan dan anonimisasi yang tidak ada di catatan ringkas.
- [ ] **Sedang — Kolom NIK tampil.** (BELUM: menunggu keputusan desa; saklarnya `ASPIRASI_NIK` di `.env`, sudah tercatat di daftar konfirmasi ke desa pada `docs/ujiManual.md`.) `desa.aspirasi.nik_aktif` aktif. Ini keputusan kebijakan desa. Jika desa tidak memerlukan NIK, mematikannya mempersingkat formulir dan mengurangi data pribadi yang disimpan.
- [x] **Selesai — Kotak teks "Isi Pesan" bisa diubah ukurannya dengan pegangan di pojok.** Di HP pegangan itu sulit dipakai dan bisa merusak tata letak. Pertimbangkan `resize: none` atau batas tinggi. Perbaikan: `max-lg:resize-none` pada `components/ui/field.blade.php`; pegangan hilang di bawah 1024 px dan tetap ada (vertikal) di desktop. Terukur di browser.
- [x] **Tidak diubah — Di desktop, kolom informasi berada di kiri dan formulir di kanan.** Pengunjung yang ingin langsung menulis harus melewati penjelasan lebih dulu. Itu pilihan desain yang sah, tetapi formulir di kiri atau lebih dekat ke judul lebih sesuai urutan baca. Sesuai Figma (node 14:1219): penjelasan di kiri, formulir di kanan.
- [x] **Tidak diubah — Teks "Dilindungi pembatasan pengiriman untuk mencegah spam"** berukuran sangat kecil dan terasa teknis bagi warga. Dapat dihilangkan atau dipindah ke bagian privasi. Teks itu ada di desain Figma (node 14:1219).
- [x] **Sudah diperiksa — Keadaan berhasil dan keadaan galat** (setelah kirim, dan pesan kesalahan per kolom). Hanya tampilan awal yang difoto. Hasil: keadaan berhasil tampil (ikon centang, penjelasan privasi, dua tombol, fokus berpindah ke kartu); keadaan galat menampilkan ringkasan galat berperan `alert`, bingkai merah, dan pesan per kolom. Keduanya baik.

---

## 4. Beranda (`/`)

Penilaian lengkap ada di percakapan sebelumnya. Ringkasan untuk daftar kerja:
- [ ] **Tinggi — Hero hanya kotak foto penampung.** Foto asli desa belum ada.
- [x] **Selesai — Ritme bagian monoton.** Statistik dan Layanan menyatu di satu latar putih dengan kartu bergaya sama. Perbaikan: bagian "Data singkat desa" menjadi pita hijau penuh dengan angka datar tanpa kartu berbingkai (`pages/public/beranda.blade.php`), memisahkannya dari "Layanan informasi". MENYIMPANG dari Figma (Figma: kartu putih, satu latar dengan Layanan), atas pilihan pengguna.
- [ ] **Sebagian — Hero tidak seimbang.** Kolom teks sempit, foto besar kosong, tombol ghost "Jelajahi profil desa" menggantung di baris sendiri. Perbaikan sebagian: teks tombol "Jelajahi profil desa" kini sejajar dengan paragraf (`lg:-ml-3.5`). Tombol itu masih turun ke baris kedua di 1440 px karena kolom teks sempit; urutan tiga tombol dalam satu baris seperti di Figma belum tercapai. Penyebabnya: di Figma tombol "Jelajahi profil desa" tanpa padding samping (sekitar 157 px), sedangkan komponen tombol kita memakai padding (sekitar 190 px), sehingga total tiga tombol melebihi kolom teks 512 px. Menyelesaikannya berarti mengubah ukuran tombol ghost atau lebar kolom hero.
- [x] **Selesai — Catatan "Survei awal 2026 (proposal); menunggu verifikasi desa" diulang empat kali** pada kartu statistik. Cukup satu catatan di bawah deretan kartu. Perbaikan: bila semua angka bersumber sama, sumber ditulis sekali ("Sumber: …") di bawah deret angka; bila berbeda, tetap per angka. `x-ui.card-stat` kini boleh tanpa `source`. Diuji.
- [x] **DIKOREKSI — Kartu UMKM:** tinggi kartu sebenarnya sama (grid meregangkan semua kartu) dan tombol WhatsApp sudah rata ke dasar kartu (`mt-auto`). Yang berbeda hanya ada atau tidaknya tombol, sesuai aturan kontak disetujui. Bukan temuan.
- [x] **DIKOREKSI — Tautan "Baca selengkapnya" pada kartu berita:** kotaknya 20 px tetapi area kliknya 60 px tingginya. Bukan temuan.

## 5. Transparansi APBDes (`/transparansi-apbdes`)

### Sudah baik
- Halaman terkuat: tiga angka ringkasan besar, dua tabel dengan baris total, batang perbandingan sederhana, dan pemilih tahun (2026/2025/2024).
- Angka rata kanan dan persentase di tiap baris. Penjelasan "Apa arti angka ini?" membantu warga awam.

### Temuan
- [x] **Selesai — Blok "Arsip dokumen" kosong terlalu besar.** Kotak bergaris putus-putus dengan ikon besar hanya untuk menyatakan "belum tersedia". Dapat dibuat jauh lebih ringkas. Perbaikan: `x-ui.empty-state` mendapat opsi `ringkas` (padding vertikal 20 px, dari 40 px), dipakai pada arsip APBDes yang kosong.
- [x] **Selesai — Pemilih tahun tidak menandai tahun tanpa data.** Pastikan tahun yang tidak punya data tidak menghasilkan halaman kosong (sudah ada `apbdes-kosong` dan `apbdes-gagal`, tetapi belum diuji tampilannya). Diperiksa: pemilih tahun hanya memuat tahun yang punya data (2026, 2025, 2024), sehingga tahun tanpa data tidak bisa dipilih; alamat dengan tahun tak dikenal (mis. `?tahun=2031`) kembali ke tahun bawaan dengan tampilan normal (sudah diuji di `PublicPagesTest`). Keadaan "Data APBDes belum dipublikasikan" (database tanpa periode) juga diperiksa di browser: judul, keterangan, tombol "Lihat Profil Desa", tanpa luapan di 1440 dan 390 px, dan kolom kontak footer tersembunyi.
- [x] **Selesai — Satu kolom tabel "Belanja" tampak kosong di bawah baris terakhir.** Tinggi kedua tabel disamakan, sehingga tabel belanja menyisakan ruang di bawah total. Perbaikan: `lg:items-start` pada grid dua tabel di `pages/public/apbdes.blade.php`; tinggi tiap kartu mengikuti isinya (458 px dan 429 px terukur), tidak lagi ada ruang kosong dalam kartu yang lebih pendek.

## 6. Potensi Desa (`/potensi-desa`)

### Sudah baik
- Tab Pertanian/UMKM dengan pencarian, kartu komoditas dengan lencana "Unggulan" dan musim panen, tabel kelompok tani.
- Kolom kontak menampilkan "Belum disetujui untuk publikasi", sesuai aturan data.

### Temuan
- [x] **Tidak diubah — Grid komoditas menyisakan sel kosong** saat jumlah kartu bukan kelipatan kolom (tadinya Singkong sendirian di baris kedua). Sifat grid kartu seragam, sama seperti grid perangkat di Profil Desa; bergantung pada jumlah komoditas yang terbit dan menyimpang dari Figma bila diubah.
- [x] **Selesai — Kolom "Kontak" pada tabel kelompok tani berisi kalimat yang sama di delapan baris.** Terbaca berisik. Cukup satu catatan di atas tabel, atau kolom disembunyikan sampai ada kontak yang disetujui. Perbaikan: kolom Kontak hanya tampil bila ada kelompok dengan kontak yang disetujui; selain itu satu catatan di bawah tabel. Diuji kedua keadaan.
- [ ] **Tinggi — Semua kartu memakai foto penampung** ("Foto jagung", "Foto padi", dan seterusnya).
- [x] **Sudah diperiksa — Tab UMKM.** Hanya tab Pertanian yang difoto. Hasil (desktop dan HP): empat kartu per halaman dengan paginasi, lencana "Menunggu data resmi desa", tombol WhatsApp hanya pada usaha yang kontaknya disetujui, tanpa luapan. Ditemukan dan diperbaiki satu hal: pencarian tanpa hasil menampilkan "Menampilkan 0–0 dari 0 UMKM". Kini hitungan dan paginasi hanya tampil bila ada hasil (`pages/public/potensi.blade.php`), diuji dua keadaan. Teks petunjuk pencarian "Cari komoditas atau usaha…" tetap karena pencarian memang mencakup keduanya.

## 7. Berita & Agenda (`/berita`) dan detail berita

### Sudah baik
- Daftar berita dua kolom dengan filter kategori, pencarian, paginasi, dan sisi agenda dengan tanggal besar.
- Detail berita nyaman dibaca: lebar teks terkendali, subjudul, kutipan bergaris aksen, tombol "Bagikan tautan", dan "Berita lainnya".
- Tautan "Agenda yang telah lewat" tersedia sebagai lipatan.

### Temuan
- [ ] **Tinggi — Foto berita penampung.** Pada detail berita foto sampul penampung menempati hampir sepertiga halaman.
- [x] **Selesai — Filter dan pencarian tidak sejajar.** Label "Kategori" ada di atas pilihan, sedangkan kolom pencarian tidak berlabel dan sejajar dengan pilihan, bukan dengan labelnya. Perbaikan: `lg:items-end` pada formulir filter di `pages/public/berita.blade.php`; dasar kotak pilihan dan kotak pencarian sama (terukur 462 px).
- [x] **Selesai — Tinggi sisi agenda tidak seimbang dengan daftar berita** saat berita lebih panjang. Pertimbangkan agenda yang tetap menempel (sticky) di layar lebar. Perbaikan: kolom agenda menempel di layar ≥ 1024 px (`lg:sticky lg:top-6`) dengan tinggi dibatasi `calc(100vh - 3rem)` dan gulir sendiri bila lebih panjang, agar bagian bawahnya tetap terjangkau. Di HP tetap statis. Terukur di browser: menempel di 24 px dari atas setelah digulir, sejajar dengan kolomnya, tanpa luapan.
- [x] **Tidak diubah — Metadata berita memuat "Admin desa".** Nama penulis generik. Ganti nama bidang resmi atau hilangkan. Teks "Admin desa" ada di desain Figma detail berita (node 14:1037), jadi dipertahankan.
- [x] **Tidak diubah — Kategori dan tanggal pada kartu berukuran sangat kecil.** Ukurannya sama dengan Figma (node 14:762 dan 14:1037).

## 8. Halaman 404

- Pesan dalam Bahasa Indonesia, ramah, dengan dua tombol ("Kembali ke Beranda" dan "Lihat Berita"). Tampilan sudah baik.
- [x] **Tidak diubah — Tidak ada breadcrumb atau pencarian.** Tidak wajib. Halaman galat lain (403, 419, 429, 500, 503) belum difoto. Tidak wajib. Halaman galat lain (403, 419, 429, 500, 503) sudah diperiksa, lihat bagian 12.

## 9. Menu HP

- Dialog layar penuh dengan ikon per menu, indikator halaman aktif, dan tombol Ruang Aspirasi di bawah. Tampilan rapi, tombol tutup besar.
- [x] **TIDAK DIUBAH — Nama menu berbeda antara desktop dan HP.** Desktop "APBDes" dan "Kontak", HP "Transparansi APBDes" dan "Kontak & Lokasi". Pilih satu penamaan. Sengaja: `Navigation::publik()` memiliki kunci `mobile` terpisah mengikuti desain Figma (menu HP memakai nama yang lebih lengkap). Mengubahnya berarti menyimpang dari Figma tanpa alasan kuat.
- [x] **Tidak diubah — Ruang kosong besar di bawah daftar menu.** Wajar untuk menu layar penuh. Tidak perlu diubah. Wajar untuk menu layar penuh.

## 10. Masuk Admin (`/admin/masuk`)

- Formulir sederhana dan terpusat, dengan label, penanda "Wajib", catatan penguncian akun, "Ingat saya", tautan "Lupa kata sandi?", dan "Kembali ke situs publik".
- [x] **DIKOREKSI — Kotak centang "Ingat saya" berukuran 20 × 20 px.** Pastikan seluruh baris label ikut bisa diklik (area sentuh minimal 44 px). Komponen `x-ui.checkbox` membungkus kotak dalam `<label>` bertinggi minimal 44 px, jadi seluruh baris dapat diklik. Bukan temuan.
- [x] **Tidak diubah — Kolom kata sandi tidak punya tombol "tampilkan kata sandi".** Berguna di HP, tetapi bukan keharusan. Tombol itu tidak ada di desain Figma halaman masuk (node 17:209).
- [x] **Tidak diubah — Keterangan "lupa kata sandi dibantu Super Admin"** berukuran sangat kecil di bawah tombol. Teks "Koneksi aman · lupa kata sandi dibantu Super Admin" ada di Figma (node 17:209) dengan ukuran sama.

---

## 11. Dashboard Admin (setelah masuk sebagai Super Admin)

Diperiksa dengan akun lokal yang Anda berikan: 14 halaman × 2 lebar layar (1440 dan 390 px). Semua menjawab 200, tidak ada luapan horizontal, tidak ada galat konsol, tidak ada teks berkontras kurang dari 4,5:1, tidak ada tautan atau tombol tanpa nama, dan tidak ada kolom formulir tanpa label. Tombol ikon sudah punya nama jelas ("Ubah berita: …", "Hapus berita: …").

### Sudah baik
- **Ringkasan:** lima kartu angka, aspirasi terbaru, pintasan, dan daftar draf terakhir. Isinya langsung berguna bagi staf. Angka aspirasi belum diproses berwarna merah dan ada lencana "4" di menu samping.
- **Daftar (Berita, Agenda, APBDes, Potensi, UMKM, Kelompok Tani, Struktur):** pencarian, filter status dan kategori, lencana status berteks (tidak hanya warna), paginasi, dan aksi ikon dengan nama. Di HP daftar menjadi kartu bertumpuk yang tetap terbaca.
- **Formulir Berita:** label dengan penanda "Wajib", penghitung karakter, editor dengan bilah format, pemilih status Draf/Terbit dengan penjelasan, kolom teks alternatif gambar yang wajib bila ada gambar, dan tiga tombol aksi yang jelas ("Batal", "Simpan sebagai Draf", "Simpan & Terbitkan").
- **Aspirasi Warga:** tata letak dua kolom (daftar dan detail), tab status dengan jumlah, ubah status dengan tombol, catatan tindak lanjut (internal), dan riwayat penanganan. NIK ditandai "tidak diisi pengirim". Catatan "Tidak dikirim otomatis ke warga" membantu staf.
- **Dashboard HP:** menu samping menjadi ikon menu, dan tombol "Lihat situs publik" serta akun tetap terjangkau.

### Temuan
- [x] **DIKOREKSI — Tombol ikon di tabel (40 × 40 px).** Kotaknya memang 40 px, tetapi `icon-button` memperluas area klik dengan `after:-inset-0.5` menjadi 44 px. Bukan temuan. Lihat koreksi di bagian pemeriksaan otomatis.
- [x] **Selesai — Tombol bilah format editor sempit:** "B" 29 px, "I" 24 px, "H2" 39 px lebarnya. Tingginya sudah 44 px (`min-h-11`), tetapi lebar belum diperluas. Memenuhi batas minimum WCAG 2.5.8 (24 px), namun di bawah aturan proyek 44 × 44 px. Perbaikan: `min-w-11` pada tombol di `components/admin/editor.blade.php`; semua tombol kini minimal 44 × 44 px.
- [x] **DIKOREKSI — Kotak pilihan status Draf/Terbit berukuran 18 × 18 px** pada formulir berita. Pastikan seluruh kartu pilihan ikut bisa diklik (tampaknya iya karena kartunya besar, tetapi belum saya klik). Setiap pilihan dibungkus `<label>` berupa kartu besar, jadi seluruh kartu dapat diklik (dibaca dari `berita-form.blade.php`). Bukan temuan.
- [x] **Selesai — Dialog terpotong di layar yang lebih pendek daripada dialognya (temuan baru).** Pada tinggi layar 600–700 px (laptop kecil dan HP), tombol "Buat Akun" di modal Tambah Akun berada di y=864 dan tidak dapat dijangkau meski digulir, karena pembungkus dialog `fixed inset-0 grid place-items-center` tidak bisa digulir. Perbaikan: `components/ui/modal.blade.php` kini memakai pembungkus `overflow-y-auto` dengan `min-h-full`; dialog tetap di tengah bila muat, dan bisa digulir sampai tombol terakhir bila tidak muat. Klik di luar kartu tetap menutup dialog. Berlaku untuk semua dialog (Tambah/Ubah Akun, hapus berita, agenda). Terukur di 1440×600, 1440×900, dan 390×700; dialog hapus tetap berfungsi.
- [x] **Selesai — Menu samping desktop terpotong di bawah.** Pada tinggi layar 900 px, item "Data Master" terpotong separuh oleh blok akun di dasar menu, dan tidak ada petunjuk bahwa daftar bisa digulir. Item di bawah "Akun & Hak Akses" juga tidak terlihat penuh. Perbaikan: kelas `.petunjuk-gulir` (bayangan tepi atas/bawah murni CSS yang hanya muncul bila ada isi tersembunyi) pada menu di `components/admin/sidebar.blade.php`. Setelah digulir, "Data Master" tampil penuh.
- [x] **DIKOREKSI — Tombol "Simpan" per dusun di Data Master 38 px tingginya**, dan "Edit" pada daftar draf di Ringkasan 38 px. Tombol memakai `size="s"` yang memperluas area klik menjadi 44 px lewat `after:-inset-1`. Bukan temuan.
- [x] **Selesai — Tautan "Lihat situs publik" di HP berukuran 42 × 44 px.** Tinggal 2 px dari aturan. Perbaikan: `min-w-11 justify-center` pada tautan di `components/admin/topbar.blade.php`; kini 44 × 44 px.
- [x] **Tidak diubah — Label menu samping berhuruf kapital kecil** ("UTAMA", "KONTEN DESA", "POTENSI", dan seterusnya). Fungsional sebagai pengelompok, tetapi kecil. Enam grup menu mengikuti desain Figma sidebar admin (node 17:78 di `docs/planFE.md`).
- [ ] **Rendah — Tab "Komoditas" dan "Kelompok Tani" di Potensi Pertanian** tampak sebagai teks biasa dengan garis bawah tipis di HP. Tab aktif kurang menonjol.
- [ ] **Rendah — "Ketua: —" di seluruh Kelompok Tani.** Data ketua belum terisi (data contoh). Tampilan "—" sudah sopan, tetapi daftar jadi berulang.
- Peran Staf sudah diperiksa, lihat bagian 13.

## 12. Keadaan lain yang kini diperiksa

- **Tab UMKM di Potensi:** tampil rapi, empat kartu per halaman dengan paginasi (1–4 dari 8 UMKM), lencana "Menunggu data resmi desa", dan tombol WhatsApp hanya pada usaha yang kontaknya disetujui.
  - [x] **DIKOREKSI — Kartu UMKM tidak sama tinggi.** Tidak benar: tinggi kartu sama dan tombol sudah rata ke dasar. Bukan temuan.
  - [x] **Tidak diubah — Teks pencarian "Cari komoditas atau usaha…" muncul di kedua tab.** Ubah petunjuk sesuai tab aktif. Pencarian memang mencakup komoditas dan usaha, jadi petunjuknya sudah tepat.
- **Aspirasi terkirim:** berhasil. Kartu "Aspirasi berhasil dikirim" tampil dengan ikon centang, penjelasan privasi, dan dua tombol ("Kirim aspirasi lain", "Kembali ke Beranda"). Fokus berpindah ke bagian itu (pembaca layar langsung membacanya). Baik.
  - [x] **Tidak diubah — Kolom formulir diganti oleh kartu ini, tetapi kolom informasi di kiri tetap sepanjang halaman.** Kartu berhasil tampak kecil dibanding kolom kiri yang panjang. Keadaan berhasil mengikuti Figma (node 14:1380 di `docs/planFE.md`).
- **Halaman 429:** tampil dengan judul "Terlalu banyak permintaan", penjelasan sopan, dan dua tombol. Sudah baik. Pembatasan bekerja sesuai konfigurasi (5 pengiriman per 10 menit): pada percobaan, permintaan ke-6 ditolak dengan 429.
- **Keyboard (beranda desktop):** urutan tab logis (Lewati ke konten, logo, menu, tombol Ruang Aspirasi, tombol di hero, kartu layanan). Setiap fokus punya outline 3 px yang terlihat. Pada akhir uji, fokus pindah ke kartu yang berada di luar layar (tergulir otomatis), yang wajar.
- **Menu HP:** fokus terkurung di dalam dialog (12 kali Tab tidak pernah keluar), Escape menutup menu, dan fokus kembali ke tombol "Buka menu". Perilaku ini benar.
  - [x] **DIKOREKSI — Pada Tab pertama di dalam menu, fokus berputar melewati logo dan tombol Tutup di urutan akhir.** Wajar bagi focus trap, tetapi tombol Tutup yang berada paling akhir sedikit janggal. Pertimbangkan menaruh tombol Tutup lebih awal dalam urutan. Salah baca: fokus awal sudah di tombol "Tutup menu" (`autofocus`), jadi Tab pertama pindah ke Beranda. Urutan sudah wajar.
- **419 (sesi berakhir):** tidak dapat dipicu dari browser. Laravel menerima permintaan POST satu-asal tanpa token (pemeriksaan asal permintaan), jadi tidak menghasilkan 419. Tampilan hanya dibaca dari kode.
- **403, 419, 500, 503:** 403 dipotret dari halaman nyata (lihat bagian 13). 419, 500, dan 503 dipotret dengan merender tampilan Blade-nya ke HTML statis (`view('errors.N')->render()`) lalu dibuka di browser, tanpa memicu galat atau mematikan server.
  - Semuanya memakai tata letak publik yang sama dengan 404: judul besar, penjelasan sopan, tombol tindakan. Teks Bahasa Indonesia baku, satu `h1`, dan tanpa luapan.
  - 419 menawarkan "Kembali ke halaman sebelumnya" dan "Ke Beranda". 500 hanya "Kembali ke Beranda" dan tidak menampilkan detail teknis. 503 hanya "Muat ulang".
  - [x] **Tidak diubah — Halaman 503 tidak menyebut kapan situs kembali.** Pertimbangkan perkiraan waktu bila diketahui. Perkiraan waktu bergantung pada data yang tidak ada; teks "Silakan kembali beberapa saat lagi" sengaja dibiarkan.
  - [x] **Selesai — Halaman 500 dan 503 hanya punya satu tombol.** Cukup, tetapi 500 bisa menambahkan jalur ke Ruang Aspirasi atau Kontak agar warga bisa melapor. Perbaikan: halaman 500 menambah tombol "Hubungi Desa" ke halaman Kontak agar warga bisa melapor (`errors/500.blade.php`); halaman 503 tetap satu tombol "Muat ulang" karena situs memang sedang tidak dilayani. Diuji.
  - [x] **Tidak diubah — Halaman galat memakai tata letak publik lengkap (header dengan semua menu).** Saat 500, menu yang masih bekerja memang berguna, tetapi tautan ke halaman yang sama-sama gagal bisa membingungkan. Sengaja: header dan footer tetap membantu warga pindah ke halaman yang masih bekerja; pada 500 kini ada juga jalur menghubungi desa.
- **Keadaan pemuatan (skeleton):** diperiksa di halaman pengembang `/_komponen`. Kartu skeleton berdenyut (`pulse`, 2 detik) dan animasi itu hampir hilang saat `prefers-reduced-motion` aktif (durasi 0,00001 detik). Sudah benar.
  - [x] **Tidak diubah — Skeleton nyaris tak terlihat terhadap latar** (abu muda pada krem). Bersifat dekoratif, tetapi tidak jelas bagi pengguna yang melihat pada layar redup. Komponen belum dipakai di halaman publik mana pun, jadi tidak berdampak; ditinjau ulang bila skeleton mulai dipakai.
  - [x] **Tidak diubah — Belum ada halaman publik yang memakai skeleton.** Komponennya hanya dipakai di halaman pengembang, jadi keadaan pemuatan belum benar-benar terjadi di situs. Tidak ada proses pemuatan lambat di halaman publik saat ini (semua dirender di server).

## 13. Peran Staf

Diperiksa dengan akun Staf sementara di database lokal: modul Berita dan Aspirasi saja. Akun itu beserta hak aksesnya sudah dihapus setelah pemeriksaan (pengguna tersisa: 1, Super Admin). Kata sandinya tidak disimpan di repo, dan skrip yang memuatnya sudah dihapus.

### Sudah baik
- **Penegakan akses benar.** Halaman Ringkasan, Berita, tambah Berita, Agenda, dan Aspirasi menjawab 200. Semua halaman lain (APBDes, Potensi, UMKM, Kelompok Tani, Struktur, Data Master, Profil, Akun) menjawab 403.
- **Menu menyesuaikan peran.** Menu samping hanya memuat Ringkasan, Berita, Agenda, dan Aspirasi Warga. Kelompok "Konten Desa", "Potensi", dan "Administrasi · Super Admin" tidak tampil.
- **Ringkasan menyesuaikan.** Hanya tiga kartu angka (berita terbit, berita draf, aspirasi belum diproses), dan "Pintasan" hanya berisi Tambah berita dan Tambah agenda. Peran tampil sebagai "Staf" di blok akun.
- **Halaman 403 ramah**, berbahasa Indonesia, dengan "Kembali ke Beranda" dan "Ke Dashboard". Di HP tersusun satu kolom tanpa luapan.

### Temuan
- [x] **Selesai — Halaman 403 tampil dengan tata letak situs publik**, bukan kerangka admin. Staf yang tersasar ke modul terlarang kehilangan menu sampingnya dan harus menekan "Ke Dashboard". Pertimbangkan 403 dalam kerangka admin untuk pengguna yang sudah masuk. Perbaikan: `errors/403.blade.php` memakai kerangka admin bagi petugas yang sudah masuk di jalur `/admin`, dan tata letak publik bagi pengunjung lain.
- [x] **Tidak diubah — Kartu "Pintasan" menyisakan ruang kosong** karena hanya dua item, sedangkan kartu aspirasi di sampingnya lebih tinggi. Hasil langsung dari jumlah modul yang dimiliki Staf (dua pintasan); memaksa mengisinya berarti menampilkan tautan ke modul yang tidak boleh dibuka.
- [x] **Selesai — Tidak ada penjelasan modul apa yang dimiliki Staf.** Pesan 403 hanya berkata "tidak memiliki izin". Daftar singkat modul yang tersedia di Ringkasan akan membantu. Perbaikan: Ringkasan untuk Staf kini menulis "Modul Anda: …" (atau "Belum ada modul yang ditugaskan kepada Anda. Hubungi Super Admin desa." bila kosong) di bawah sapaan (`pages/admin/dashboard.blade.php`). Super Admin tidak melihat baris itu. Diuji tiga keadaan.
- Hanya satu kombinasi modul yang diuji (Berita dan Aspirasi). Kombinasi lain mengikuti aturan yang sama di kode (`punyaModul`), tetapi belum diuji satu per satu di browser.

---

## Hasil pemeriksaan otomatis (11 alamat × 2 lebar layar)

Lolos di semua halaman:
- Tepat satu `h1` per halaman, `<main>` ada, atribut `lang="id"` benar, tidak ada lompatan tingkat judul.
- Tidak ada luapan horizontal di 390 px maupun 1440 px.
- Tidak ada tautan atau tombol tanpa nama, dan tidak ada kolom formulir tanpa label (selain kolom jebakan spam yang memang tersembunyi dari manusia).
- Seluruh teks lolos kontras 4,5:1 kecuali satu temuan di bawah. Dari 11 halaman tidak ada satu pun teks situs sendiri yang gagal.
- Tidak ada `<img>` tanpa `alt`. Halaman saat ini tidak punya foto sungguhan sama sekali, sehingga atribut ukuran gambar belum relevan.

Temuan:
- [x] **Selesai — Atribusi peta ("Leaflet", "Esri") berkontras 3,64:1** (biru `rgb(0,120,168)` pada abu `rgb(221,221,221)`). Berasal dari gaya bawaan Leaflet di Profil dan Kontak. Perbaikan di `resources/css/app.css`: tautan memakai warna `primary` bergaris bawah, teks memakai warna `text`, dan latar atribusi 0,92 putih. Atribusi tetap tampil. Terverifikasi di browser (latar `rgba(255,255,255,0.92)`, tautan `rgb(27,94,32)`).
- [x] **Selesai — Kontrol zoom peta berukuran 30 × 30 px.** Kini 44 × 44 px di desktop dan HP (terukur di browser).
- **KOREKSI — target sentuh yang sebelumnya saya sebut kurang dari 44 px ternyata sebagian besar sudah memenuhi aturan.** Pemeriksaan otomatis awal hanya mengukur kotak elemen, padahal komponen sengaja memperluas area klik dengan elemen bayangan (`after:-inset-*`). Saya menguji ulang dengan membaca titik di luar kotak (`elementFromPoint`), hasilnya:

  | Elemen | Kotak terukur | Area klik sebenarnya | Status |
  |---|---|---|---|
  | Header: Ruang Aspirasi (desktop) | 154 × 36 | 162 × 44 | Lolos |
  | Breadcrumb: Beranda | 55 × 29 | 63 × 45 | Lolos |
  | Kartu berita: Baca selengkapnya | 308 × 20 | 348 × 60 | Lolos |
  | Bagikan tautan | 131 × 38 | 137 × 44 | Lolos |
  | Footer: tautan, HP | 358 × 44 | 358 × 44 | Lolos |
  | Footer: tautan, desktop | 341 × 33 | 341 × 33 | Belum, sengaja (`md:min-h-0`), pointer mouse |
  | Leaflet: zoom + | 30 × 30 | 30 × 30 | Belum (bawaan Leaflet) |
  | Login: kotak centang | 20 × 20 | 20 × 20 (kotaknya saja) | Perlu cek label |

  Tombol ikon admin (`icon-button`: `size-10` + `after:-inset-0.5`) dan tombol ukuran kecil (`min-h-9` + `after:-inset-1`) juga memakai pola yang sama, sehingga area kliknya 44 px menurut kodenya. Itu dibaca dari kode, bukan diuji di browser. Yang benar-benar tersisa: zoom peta, tautan footer di desktop, lebar tombol bilah editor, dan kotak centang tanpa label yang bisa diklik.
- [x] **DIKOREKSI — Kotak centang "Ingat saya" 20 × 20 px** pada halaman masuk (lihat bagian 10). Belum diuji apakah mengeklik tulisan "Ingat saya" ikut mencentangnya. Lihat koreksi di bagian 10: seluruh baris label dapat diklik.

Bukan masalah (hasil pemeriksaan otomatis yang saya tinjau dan tolak):
- Kolom `situs_web` di Ruang Aspirasi: kolom jebakan spam yang sengaja tersembunyi dan ber-`aria-hidden`, bukan kolom tanpa label.
- Gambar tanpa ukuran di Profil dan Kontak: ubin peta Leaflet, bukan gambar situs.
- Dua `<nav aria-label="Navigasi utama">`: di setiap lebar layar hanya satu yang tampil (desktop atau menu HP), jadi tidak ada landmark ganda yang terbaca pembaca layar. Temuan ini dari pembacaan kode awal, sekarang dicabut.

---

## Temuan lintas halaman

- [ ] **Tinggi — Gambar dan foto asli belum ada di seluruh situs.** Hero Beranda, kartu berita, UMKM, komoditas, dan foto perangkat semuanya penampung. Ini hambatan visual terbesar dan hanya bisa diselesaikan dengan foto asli yang disetujui desa.
- [ ] **Sedang — Pola kartu putih berbingkai tipis berulang di semua halaman.** Variasikan satu atau dua bagian dengan latar warna penuh atau tata letak asimetris agar ritmenya tidak monoton.
- [ ] **Sedang — Belum diuji dengan pembaca layar atau hanya dengan keyboard.** Indikator fokus (`:focus-visible`, outline 3 px) dan `prefers-reduced-motion` sudah ada di `app.css`, tetapi urutan tab dan perilaku dialog belum dijalankan langsung.
- [x] **Selesai — `text-wrap: balance` pada judul dan `tabular-nums` pada angka** belum dipakai. Polesan kecil. Atribut `width` dan `height` pada `<img>` perlu ditambahkan saat foto asli masuk (`beranda.blade.php:80` dan komponen kartu). Perbaikan: `h1`, `h2`, `h3` memakai `text-wrap: balance` (aturan dasar di `resources/css/app.css`); angka pada `x-ui.card-stat`, pita statistik Beranda, dan tabel APBDes memakai `tabular-nums`. Terukur di browser: `text-wrap: balance` dan `tabular-nums` aktif. (Atribut `width` dan `height` pada `<img>` tetap menunggu foto asli.)

## Catatan metode dan batasan
- Penilaian ini berasal dari tangkapan layar, pembacaan kode, dan pemeriksaan otomatis lewat skrip Playwright. Skrip `inspect.mjs` milik taste-skill tidak dijalankan, jadi tidak ada angka ukur untuk lebar margin dan tinggi bagian kosong.
- Kontras dihitung dari warna terhitung (computed style) dengan rumus WCAG pada setiap teks yang tampil. Latar yang transparan ditelusuri ke latar induk terdekat, jadi teks di atas gambar atau gradien tidak terwakili (situs ini belum punya).
- Bagian dalam admin diperiksa dengan akun Super Admin lokal yang Anda berikan. Kredensial itu hanya dipakai untuk membuka `127.0.0.1` dan tidak disimpan di repo (skrip login sementara sudah dihapus). Tidak ada tombol Simpan atau Hapus yang ditekan, dan tidak ada akun yang dibuat atau diubah.
- Satu-satunya data yang ditulis saat pemeriksaan: satu aspirasi uji ("Uji Tampilan") untuk melihat keadaan berhasil. Baris itu dan riwayatnya sudah dihapus lagi. Pembatasan pengiriman (5 per 10 menit per IP) sempat aktif di database lokal selama pengujian 429 dan akan berakhir dengan sendirinya.
- Peran Staf diperiksa dengan akun sementara (modul Berita dan Aspirasi) yang dibuat lalu dihapus lagi di database lokal. Pengguna tersisa: 1.
- Halaman 419, 500, dan 503 dipotret dengan merender tampilan Blade ke HTML statis, bukan dengan memicu galat atau mematikan server (sesuai permintaan Anda). Halaman 419 juga tidak bisa dipicu dari browser karena Laravel menerima POST satu-asal tanpa token.
- **Belum diperiksa:** kombinasi modul Staf selain Berita + Aspirasi, dan tampilan Staf dengan modul Akun (tidak bisa ditugaskan ke Staf menurut kode). Selebihnya yang terdaftar di awal sudah tercakup.
- Skill taste-skill mendorong gaya yang dapat bertentangan dengan Figma dan DESIGN.md. Semua usulan di atas bersifat masukan, bukan perintah. Acuan tetap desain Figma dan aturan proyek.
