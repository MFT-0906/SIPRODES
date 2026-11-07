# tugasIsiData.md — Tugas Mengisi Data Web (Tanpa Menunggu Desa)

Daftar tugas untuk mengisi konten lewat halaman admin (`/admin`) sebelum data resmi dari Pemerintah Desa tersedia. Centang `[x]` setelah selesai.

**Aturan utama**
- Isi hanya yang bisa dibuktikan dari sumber resmi atau yang Anda lihat sendiri. Jangan mengarang angka, nama, atau tanggal.
- Ragu = simpan sebagai **Draf**. Konten draf tidak tampil di situs publik.
- Foto harus asli (milik sendiri atau berizin). Tanpa foto AI atau stok. Orang yang tampak di foto harus setuju.
- Data yang diisi di komputer lokal hanya ada di database lokal. Isi ulang di server produksi atau pindahkan lewat dump database.
- Kontak, alamat, dan peta baru tampil di publik bila kotak "sudah diverifikasi" dicentang **dan** disimpan lewat "Simpan & Terbitkan".

---

## A. Bisa dikerjakan sekarang

### A1. Lokasi dan alamat — Profil → Lokasi & Geografis
- [x] Cari kantor Desa Marga Agung di Google Maps, klik kanan titik kantor, salin koordinat.
- [x] Isi **Lintang** (contoh bentuk: `-5.xxxxx`) dan **Bujur** (contoh bentuk: `105.xxxxx`).
- [x] Isi **Alamat kantor desa** sesuai papan nama atau surat resmi.
- [x] Klik **Simpan Draf** (bukan Terbitkan). Kotak "sudah diverifikasi" **jangan dicentang** dulu.
- [x] Periksa tautan "Buka titik di Google Maps" di halaman itu: titik harus jatuh di kantor desa.
- [x] Setelah desa membenarkan, centang verifikasi lalu **Simpan & Terbitkan**. Cek `/kontak-lokasi` dan footer. *(Sudah diterbitkan; konfirmasi alamat dan kode pos ke desa tetap diperlukan.)*

### A2. Sejarah desa — Profil → Sejarah
- [x] Rapikan naskah dari proposal (transmigrasi bedol desa dari Kaligesik, Srumbung, Magelang ke Jati Agung, Lampung Selatan).
- [x] Gunakan baris kosong untuk memisah paragraf (paragraf pertama menjadi lead di publik).
- [x] Jangan menambah fakta atau tahun yang belum ada sumbernya.
- [x] Simpan, lalu cek tampilan di `/profil-desa`. *(Naskah berisi tiga paragraf dari fakta proposal saja; tahun dan tokoh belum ada sumbernya, jadi belum ditambahkan.)*

### A3. Data ringkas — Profil → Data Ringkas
- [x] Biarkan angka awal (± 4.492 jiwa, 1.309 KK, 1.050 ha, 6 dusun) bila belum ada koreksi. *(Terisi: 4.492 jiwa, 1.309 KK, 1.050 ha, 6 dusun, mata pencaharian Petani.)*
- [x] Isi **Sumber data** dengan jelas, mis. "Survei awal 2026 (proposal); menunggu verifikasi desa".
- [x] **Jangan** centang "sudah diverifikasi" sampai desa mengonfirmasi (tanda "±" akan hilang setelah dicentang).

### A4. Berita dan agenda asli — Berita, Agenda
- [ ] Tulis 2–3 berita dari kegiatan yang benar-benar Anda ketahui atau dokumentasikan.
- [ ] Gunakan foto asli, isi teks alternatif (alt) yang menjelaskan isi gambar; foto ≤ 2 MB.
- [ ] Pilih kategori (Kegiatan, Pertanian, Pemerintahan, Pengumuman).
- [ ] Simpan sebagai **Draf** dulu, baca ulang, lalu **Simpan & Terbitkan**.
- [ ] Agenda: isi hanya kegiatan yang tanggalnya pasti. Waktu dan lokasi boleh dikosongkan bila belum pasti.
- [ ] Hapus atau ubah berita/agenda **contoh** yang masih ada (berlabel contoh) agar tidak tercampur data nyata.

### A5. Komoditas — Potensi Pertanian
- [x] Ubah komoditas **Jagung**: deskripsi umum yang benar, tandai **unggulan**. *(Terisi: deskripsi umum netral, unggulan; musim panen kosong.)*
- [ ] Unggah foto jagung asli + alt. **Musim panen** kosongkan bila belum dikonfirmasi petani.
- [x] Simpan sebagai Terbit bila sudah yakin, atau Draf bila belum. *(Disimpan sebagai Draf; terbitkan setelah foto asli diunggah.)*

### A6. Akun admin — Akun & Hak Akses
- [ ] Buat akun untuk perangkat desa yang akan mengelola konten (email aktif, peran Staf).
- [ ] Tugaskan modul seperlunya (Berita, Potensi, Profil, APBDes, Aspirasi). Akun & Data Master khusus Super Admin.
- [ ] Pastikan email undangan sampai dan penerima bisa membuat kata sandi sendiri.

### A7. Data master — Data Master (Super Admin)
- [x] Ubah nama dusun dan jabatan **hanya bila** Anda sudah tahu nama resminya. Bila belum, biarkan nilai bawaan (Dusun 1–6 dan 8 jabatan). *(Nilai bawaan sudah terisi: Dusun 1–6 dan 8 jabatan; ganti ke nama resmi setelah daftar dari desa ada.)*

---

## B. Tunggu data resmi dari desa

| Data | Alasan menunggu | Di mana diisi |
|---|---|---|
| **Visi dan misi** | Harus sesuai dokumen RPJMDes | Profil → Visi & Misi |
| **Nama dan foto perangkat desa** | Nama sah dan izin foto | Struktur Perangkat |
| **Angka APBDes dan dokumen PDF** | Harus dari dokumen yang sudah ditetapkan | APBDes |
| **Telepon, email, jam layanan** | Belum dikonfirmasi desa | Profil → Lokasi & Geografis |
| **Kelompok tani** (nama, ketua, kontak) | Perlu bukti persetujuan kontak | Kelompok Tani |
| **UMKM** (usaha, produk, WhatsApp, foto) | Perlu persetujuan pemilik usaha | UMKM |

---

## C. Daftar pertanyaan untuk perangkat desa

Serahkan daftar ini agar data resmi cepat terkumpul.

- [ ] Naskah **visi dan misi** resmi (sesuai RPJMDes yang berlaku).
- [ ] Daftar **perangkat desa**: nama lengkap sesuai SK, jabatan, dan foto (dengan persetujuan).
- [ ] Daftar **dusun** dan **jabatan** resmi.
- [ ] **Alamat kantor**, nomor telepon, email resmi, dan jam layanan yang boleh dipublikasikan.
- [ ] Dokumen **APBDes** yang sudah ditetapkan (PDF) beserta rincian pendapatan, belanja per bidang, dan pembiayaan, untuk tahun berjalan dan tahun sebelumnya bila ada.
- [ ] Data **kelompok tani**: nama, komoditas, dusun, ketua, dan kontak yang **disetujui** untuk tampil di publik.
- [ ] Data **UMKM**: nama usaha, produk, deskripsi, nomor WhatsApp, dan foto produk dengan **izin pemilik**.
- [ ] Data penduduk, KK, dan luas wilayah yang sudah diverifikasi, beserta sumber dan tahunnya.
- [ ] Siapa yang berwenang **menerbitkan** konten dan siapa yang membaca **Ruang Aspirasi**.
- [ ] Kebijakan: apakah **NIK** pada aspirasi dikumpulkan (`ASPIRASI_NIK`) dan berapa lama aspirasi disimpan (bawaan 24 bulan).
- [ ] Persetujuan teks Beranda (judul sambutan, pengantar Potensi, ajakan Aspirasi) yang saat ini tertulis di kode.

---

## D. Setelah data diisi

- [ ] Buka semua halaman publik (Beranda, Profil, APBDes, Potensi, Berita, Kontak, Aspirasi) di HP dan komputer; pastikan tidak ada penanda "menunggu data resmi" yang tersisa di data yang sudah resmi.
- [ ] Pastikan tidak ada data berlabel **contoh** yang masih tampil di publik.
- [ ] Buka menu **Akun & Hak Akses → Log Aktivitas** untuk memeriksa siapa mengubah apa.
- [ ] Bila akan rilis: ikuti `docs/deploy.md` dan checklist di `docs/ujiManual.md`.
