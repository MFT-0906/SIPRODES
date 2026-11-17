@extends('layouts.public')

@section('title', 'Kebijakan Privasi')
@section('description', 'Data pribadi apa saja yang dikumpulkan situs Desa Marga Agung, untuk apa dipakai, berapa lama disimpan, dan hak Anda atas data tersebut.')

@php
    $kontak = $identitas['kontak'];
    $email = $kontak['email']['value'] ?? null;
    $telepon = $kontak['telepon']['value'] ?? null;
    $alamat = $kontak['alamat']['value'] ?? null;
    $namaDesa = $identitas['sebutan'].' '.$identitas['nama'];
@endphp

@section('content')
    <x-public.kepala-halaman jejak="Kebijakan Privasi" judul="Kebijakan Privasi" pengantar="Penjelasan sederhana tentang data pribadi yang diproses situs {{ $namaDesa }}, tujuannya, dan hak Anda." />

    <div class="bg-surface">
        <div class="container-page pb-14 pt-8 lg:pb-20 lg:pt-12">
            <article class="mx-auto flex max-w-[760px] flex-col gap-10">
                <p class="type-body-sm text-muted">Berlaku sejak 17 November 2026.</p>

                <section aria-labelledby="priv-pengelola" class="flex flex-col gap-3">
                    <h2 id="priv-pengelola" class="type-h2 text-text">Siapa pengelola situs ini</h2>
                    <p class="type-body text-muted">Situs ini dikelola oleh {{ $namaDesa }}, {{ $identitas['wilayah'] }} Pengelola menentukan data apa yang dikumpulkan dan untuk apa dipakai.</p>
                    @if ($email || $telepon || $alamat)
                        <ul class="flex flex-col gap-1 type-body text-muted">
                            @if ($alamat)<li>Alamat kantor: {{ $alamat }}</li>@endif
                            @if ($telepon)<li>Telepon: {{ $telepon }}</li>@endif
                            @if ($email)<li>Email: <a href="mailto:{{ $email }}" class="text-primary underline underline-offset-4">{{ $email }}</a></li>@endif
                        </ul>
                    @else
                        <p class="type-body text-muted">Kontak resmi desa ditampilkan di halaman <a href="{{ route('kontak') }}" class="text-primary underline underline-offset-4">Kontak &amp; Lokasi</a> setelah diverifikasi oleh desa.</p>
                    @endif
                </section>

                <section aria-labelledby="priv-pengunjung" class="flex flex-col gap-3">
                    <h2 id="priv-pengunjung" class="type-h2 text-text">Pengunjung biasa</h2>
                    <p class="type-body text-muted">Anda dapat membaca halaman publik tanpa mendaftar dan tanpa memberikan data pribadi. Situs ini tidak memakai layanan analitik, iklan, atau pelacak pihak ketiga.</p>
                </section>

                <section aria-labelledby="priv-aspirasi" class="flex flex-col gap-3">
                    <h2 id="priv-aspirasi" class="type-h2 text-text">Ruang Aspirasi</h2>
                    <p class="type-body text-muted">Saat Anda mengirim aspirasi, data yang disimpan di basis data desa adalah:</p>
                    <ul class="flex list-disc flex-col gap-1 pl-6 type-body text-muted">
                        <li>nama yang Anda tulis (wajib diisi; boleh nama panggilan);</li>
                        <li>isi pesan Anda;</li>
                        <li>status penanganan (baru, dibaca, atau selesai), beserta waktu aspirasi diterima, dibaca, dan selesai;</li>
                        <li>riwayat penanganan: tindakan dan catatan petugas desa.</li>
                    </ul>
                    <p class="type-body text-muted">Formulir tidak meminta NIK, nomor telepon, atau email. Alamat IP dan jenis peramban Anda tidak disimpan bersama aspirasi. Mohon tidak menuliskan data yang sangat pribadi di dalam isi pesan, karena isi pesan tetap tersimpan sebagaimana Anda tulis.</p>
                    <p class="type-body text-muted"><strong class="text-text">Tujuan:</strong> menerima, menindaklanjuti, dan mencatat pengaduan atau saran warga.</p>
                    <p class="type-body text-muted"><strong class="text-text">Siapa yang melihat:</strong> hanya admin desa yang diberi akses ke modul aspirasi. Aspirasi tidak pernah ditampilkan di halaman publik.</p>
                    <p class="type-body text-muted"><strong class="text-text">Perlindungan dari spam:</strong> untuk membatasi pengiriman berlebihan, sistem menghitung jumlah kiriman per alamat IP dalam bentuk sandi acak (hash) yang tersimpan sementara di cache situs. Sistem juga mencatat sementara, di sesi Anda, waktu pengisian formulir dan jawaban pertanyaan hitungan. Data ini tidak disimpan bersama aspirasi.</p>
                </section>

                <section aria-labelledby="priv-turnstile" class="flex flex-col gap-3">
                    <h2 id="priv-turnstile" class="type-h2 text-text">Cloudflare Turnstile</h2>
                    <p class="type-body text-muted">Bila diaktifkan, formulir Ruang Aspirasi memakai Cloudflare Turnstile untuk membedakan manusia dari robot spam. Widget ini dimuat dari server Cloudflare, sehingga Cloudflare dapat memproses data teknis perangkat dan peramban Anda. Situs ini tidak mengirimkan alamat IP Anda ke Cloudflare saat memverifikasi hasilnya. Bila widget tidak dapat dimuat atau Turnstile tidak aktif, formulir memakai pertanyaan hitungan sederhana sebagai gantinya. Pengelolaan data oleh Cloudflare diatur dalam <a href="https://www.cloudflare.com/privacypolicy/" target="_blank" rel="noopener noreferrer" class="text-primary underline underline-offset-4">kebijakan privasi Cloudflare<span class="sr-only"> (terbuka di tab baru)</span></a>.</p>
                </section>

                <section aria-labelledby="priv-admin" class="flex flex-col gap-3">
                    <h2 id="priv-admin" class="type-h2 text-text">Akun admin dan log aktivitas</h2>
                    <p class="type-body text-muted">Bagian ini hanya berlaku bagi petugas desa yang memiliki akun admin. Akun dibuat oleh Super Admin. Data yang disimpan:</p>
                    <ul class="flex list-disc flex-col gap-1 pl-6 type-body text-muted">
                        <li>nama, alamat email, dan nama pengguna (bila diisi);</li>
                        <li>kata sandi, hanya dalam bentuk sandi acak (hash), bukan teks asli;</li>
                        <li>peran, status aktif, modul yang boleh diakses, dan waktu terakhir masuk;</li>
                        <li>ID akun Google, bila admin memakai "Masuk dengan Google" (lihat bagian berikut).</li>
                    </ul>
                    <p class="type-body text-muted">Tindakan penting di dashboard (misalnya masuk, mengubah konten, atau mengubah akun) dicatat di log aktivitas: jenis tindakan, objek yang diubah, keterangan singkat, alamat IP, serta nama dan email pelaku. Log dipakai untuk keamanan dan pertanggungjawaban, dan hanya dapat dilihat admin yang berwenang.
                        @if ($masaSimpanLogBulan > 0)
                            Entri log yang lebih tua dari {{ $masaSimpanLogBulan }} bulan dihapus otomatis.
                        @else
                            Entri log saat ini tidak dihapus otomatis.
                        @endif
                        Bila sebuah akun dihapus, catatan log lama tetap menyimpan salinan nama dan emailnya sampai entri log tersebut dihapus.</p>
                </section>

                <section aria-labelledby="priv-google" class="flex flex-col gap-3">
                    <h2 id="priv-google" class="type-h2 text-text">Masuk dengan Google (khusus admin)</h2>
                    <p class="type-body text-muted">Admin dapat masuk dengan akun Google bila fitur ini diaktifkan. Saat itu Google memberikan kepada situs: ID akun Google, alamat email, status verifikasi email, dan nama. Data tersebut hanya dipakai untuk mencocokkan email dengan akun admin yang sudah didaftarkan Super Admin.</p>
                    <ul class="flex list-disc flex-col gap-1 pl-6 type-body text-muted">
                        <li>Google tidak pernah membuat akun baru di situs ini. Email yang tidak terdaftar atau akun yang nonaktif ditolak.</li>
                        <li>Yang disimpan di akun admin hanya ID akun Google, agar login berikutnya hanya diterima dari akun Google yang sama. Nama dari Google tidak disimpan. Bila login ditolak, email yang dicoba dicatat di log aktivitas.</li>
                        <li>Situs hanya meminta izin dasar (identitas, email, dan profil). Situs tidak mengakses Gmail, Google Drive, kalender, atau data Google lainnya.</li>
                        <li>Data dari Google tidak dibagikan ke pihak lain.</li>
                        <li>Tautan akun Google dapat dilepas oleh Super Admin, dan ikut terlepas bila email akun diganti.</li>
                    </ul>
                    <p class="type-body text-muted">Pemrosesan data oleh Google diatur dalam <a href="https://policies.google.com/privacy" target="_blank" rel="noopener noreferrer" class="text-primary underline underline-offset-4">kebijakan privasi Google<span class="sr-only"> (terbuka di tab baru)</span></a>.</p>
                </section>

                <section aria-labelledby="priv-peta" class="flex flex-col gap-3">
                    <h2 id="priv-peta" class="type-h2 text-text">Peta lokasi</h2>
                    <p class="type-body text-muted">Halaman Profil Desa dan Kontak &amp; Lokasi dapat menampilkan peta interaktif. Gambar peta diambil dari server Esri (ArcGIS), sehingga peramban Anda menghubungi server tersebut dan Esri dapat menerima alamat IP Anda. Peta tidak meminta lokasi perangkat Anda.</p>
                </section>

                <section aria-labelledby="priv-cookie" class="flex flex-col gap-3">
                    <h2 id="priv-cookie" class="type-h2 text-text">Cookie</h2>
                    <p class="type-body text-muted">Situs ini hanya memakai cookie yang diperlukan agar berfungsi: cookie sesi dan cookie keamanan (CSRF) untuk melindungi formulir. Tidak ada cookie iklan atau pelacak.</p>
                    <p class="type-body text-muted">Sesi disimpan di basis data bersama alamat IP dan jenis peramban, dan berlaku sampai {{ $masaSesiMenit }} menit tidak aktif. Sesi yang sudah kedaluwarsa dibersihkan otomatis secara berkala.</p>
                </section>

                <section aria-labelledby="priv-simpan" class="flex flex-col gap-3">
                    <h2 id="priv-simpan" class="type-h2 text-text">Penyimpanan dan keamanan data</h2>
                    <ul class="flex list-disc flex-col gap-1 pl-6 type-body text-muted">
                        <li>
                            Aspirasi:
                            @if ($masaSimpanAspirasiBulan > 0)
                                {{ $masaSimpanAspirasiBulan }} bulan setelah aspirasi dinyatakan selesai, nama pengirim dihapus otomatis (data lama yang masih memuat NIK juga dihapus). Isi pesan dan riwayat penanganan tetap disimpan sebagai arsip tanpa identitas pengirim. Aspirasi yang belum selesai disimpan selama masih ditangani.
                            @else
                                saat ini tidak ada penghapusan atau anonimisasi otomatis; data disimpan selama diperlukan untuk pelayanan.
                            @endif
                        </li>
                        <li>Akun admin disimpan selama akun dipakai dan dihapus bila Super Admin menghapus akun tersebut.</li>
                        <li>Data lain disimpan selama diperlukan untuk pelayanan dan dapat dihapus atas permintaan, sepanjang tidak bertentangan dengan kewajiban pengarsipan desa.</li>
                    </ul>
                    <p class="type-body text-muted">Pengamanan yang dipakai antara lain: kata sandi di-hash, NIK data lama dienkripsi, koneksi HTTPS di server produksi, pembatasan hak akses admin per modul, serta pembatasan percobaan login dan pengiriman formulir.</p>
                </section>

                <section aria-labelledby="priv-hak" class="flex flex-col gap-3">
                    <h2 id="priv-hak" class="type-h2 text-text">Hak Anda sebagai pemilik data</h2>
                    <p class="type-body text-muted">Sesuai Undang-Undang Nomor 27 Tahun 2022 tentang Pelindungan Data Pribadi, Anda berhak meminta akses atas data pribadi Anda, meminta perbaikan bila ada yang keliru, dan meminta penghapusan data Anda.</p>
                    <p class="type-body text-muted">Karena aspirasi tidak memakai akun, sebutkan nama yang Anda pakai dan waktu kira-kira Anda mengirim agar petugas dapat menemukannya.
                        @if ($email || $telepon)
                            Hubungi desa melalui
                            {{ collect([$telepon ? 'telepon '.$telepon : null, $email ? 'email '.$email : null])->filter()->implode(' atau ') }},
                            atau datang langsung ke kantor desa.
                        @else
                            Hubungi desa lewat halaman <a href="{{ route('kontak') }}" class="text-primary underline underline-offset-4">Kontak &amp; Lokasi</a> atau datang langsung ke kantor desa.
                        @endif
                    </p>
                </section>

                <section aria-labelledby="priv-ubah" class="flex flex-col gap-3">
                    <h2 id="priv-ubah" class="type-h2 text-text">Perubahan kebijakan</h2>
                    <p class="type-body text-muted">Bila kebijakan ini berubah, versi terbaru ditampilkan di halaman ini beserta tanggal berlakunya.</p>
                </section>
            </article>
        </div>
    </div>
@endsection
