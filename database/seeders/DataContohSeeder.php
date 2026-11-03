<?php

namespace Database\Seeders;

use App\Enums\JenisAnggaran;
use App\Enums\StatusAspirasi;
use App\Enums\StatusTerbit;
use App\Models\Agenda;
use App\Models\ApbdesPeriode;
use App\Models\Aspirasi;
use App\Models\Berita;
use App\Models\Jabatan;
use App\Models\KelompokTani;
use App\Models\Komoditas;
use App\Models\PerangkatDesa;
use App\Models\Umkm;
use Illuminate\Database\Seeder;

/**
 * Data contoh yang dipindahkan dari prototipe frontend. Semua baris bertanda `contoh = true`
 * dan BUKAN data resmi desa; hapus atau ganti sebelum rilis. Tidak dijalankan di produksi.
 */
class DataContohSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(MasterDataSeeder::class);

        $this->berita();
        $this->agenda();
        $this->perangkat();
        $this->potensi();
        $this->apbdes();
        $this->aspirasi();
    }

    private function berita(): void
    {
        $data = [
            ['gotong-royong-lingkungan-bersih', 'Gotong royong, lingkungan bersih dan warga makin akrab', 'Warga bersama-sama membersihkan jalan dan saluran air di lingkungan desa.', 'Kegiatan', '2026-10-18', StatusTerbit::Terbit, implode("\n\n", [
                'Lingkungan yang nyaman dimulai dari kepedulian bersama. Pagi itu, warga berkumpul untuk membersihkan jalan dan saluran air di sekitar permukiman. Kegiatan sederhana menjadi kesempatan untuk saling menyapa dan bekerja bersama.',
                'Peserta membawa peralatan sesuai kebutuhan, seperti sapu, pengki, dan karung untuk mengumpulkan sampah. Sebelum memulai, warga membagi tugas agar pekerjaan lebih ringan dan area dapat ditangani dengan tertib.',
                '## Berbagi tugas, meringankan pekerjaan',
                'Sebagian warga menyapu jalan, sementara yang lain mengangkat daun serta sampah dari saluran air. Sampah dikumpulkan di tempat yang telah disepakati agar tidak kembali masuk ke selokan.',
                '> Lingkungan terawat adalah tanggung jawab bersama.',
                '## Menjaga kebiasaan baik',
                'Setelah area dibersihkan, warga dapat membuat kesepakatan sederhana: tidak membuang sampah ke saluran air dan mengumpulkan sampah rumah tangga pada tempatnya.',
            ])],
            ['mengolah-jagung-peluang-usaha', 'Mengolah jagung menjadi peluang usaha bersama', 'Cerita tentang hasil kebun yang dapat diolah menjadi produk bernilai tambah.', 'Pertanian', '2026-10-15', StatusTerbit::Terbit, null],
            ['ruang-musyawarah-usulan-warga', 'Ruang musyawarah untuk mendengar usulan warga', 'Warga berbagi gagasan untuk lingkungan dan pelayanan yang lebih baik.', 'Pemerintahan', '2026-10-12', StatusTerbit::Terbit, null],
            ['jadwal-pelayanan-kantor-desa-november', 'Jadwal pelayanan kantor desa bulan November', 'Informasi hari dan jam pelayanan administrasi untuk warga.', 'Pengumuman', '2026-10-08', StatusTerbit::Terbit, null],
            ['draf-belum-terbit', 'Draf berita yang belum diterbitkan', 'Naskah masih disunting oleh perangkat desa.', 'Pengumuman', '2026-10-20', StatusTerbit::Draf, null],
        ];

        foreach ($data as [$slug, $judul, $ringkasan, $kategori, $tanggal, $status, $isi]) {
            Berita::query()->updateOrCreate(['slug' => $slug], [
                'judul' => $judul,
                'kategori' => $kategori,
                'ringkasan' => $ringkasan,
                'isi' => $isi ?? $ringkasan,
                'tanggal' => $tanggal,
                'status' => $status,
                'diterbitkan_at' => $status === StatusTerbit::Terbit ? $tanggal : null,
                'contoh' => true,
            ]);
        }
    }

    private function agenda(): void
    {
        $data = [
            ['2026-11-05', 'Musyawarah Desa', '09:00', 'Lokasi kegiatan', StatusTerbit::Terbit],
            ['2026-11-12', 'Posyandu', '08:00', 'Lokasi kegiatan', StatusTerbit::Terbit],
            ['2026-11-20', 'Kerja bakti', null, null, StatusTerbit::Terbit],
            ['2026-12-02', 'Penyuluhan pertanian', '13:00', 'Lokasi kegiatan', StatusTerbit::Terbit],
            ['2026-10-15', 'Posyandu', '08:00', 'Lokasi kegiatan', StatusTerbit::Terbit],
            ['2026-11-28', 'Agenda draf contoh', null, null, StatusTerbit::Draf],
        ];

        foreach ($data as [$tanggal, $judul, $waktu, $lokasi, $status]) {
            Agenda::query()->updateOrCreate(
                ['judul' => $judul, 'tanggal' => $tanggal],
                ['waktu' => $waktu, 'lokasi' => $lokasi, 'status' => $status, 'contoh' => true],
            );
        }
    }

    private function perangkat(): void
    {
        foreach (Jabatan::query()->orderBy('urutan')->get() as $jabatan) {
            PerangkatDesa::query()->firstOrCreate(
                ['jabatan_id' => $jabatan->id],
                ['nama' => 'Nama perangkat', 'urutan' => $jabatan->urutan, 'tampil' => true, 'contoh' => true],
            );
        }
    }

    private function potensi(): void
    {
        $jagung = Komoditas::query()->firstOrCreate(['nama' => 'Jagung'], [
            'deskripsi' => 'Komoditas utama dan produk unggulan Desa Marga Agung. Jagung dapat menjadi bahan pangan dan bahan baku usaha rumahan dengan nilai tambah.',
            'musim_panen' => null,
            'foto_alt' => 'Foto jagung',
            'unggulan' => true,
            'status' => StatusTerbit::Terbit,
            'contoh' => true,
        ]);

        foreach (['Kelompok Tani 1', 'Kelompok Tani 2'] as $nama) {
            KelompokTani::query()->firstOrCreate(['nama' => $nama], [
                'komoditas_id' => $jagung->id,
                'dusun_id' => null,
                'kontak' => null,
                'kontak_disetujui' => false,
                'contoh' => true,
            ]);
        }

        $umkm = [
            ['Dapur Jagung Bersama', 'Keripik jagung renyah', 'Camilan olahan jagung untuk teman minum teh dan bingkisan keluarga.'],
            ['Anyaman Sahabat Desa', 'Keranjang & wadah anyaman', 'Kerajinan anyaman untuk kebutuhan rumah dan kemasan hadiah.'],
            ['Usaha warga 3', 'kerajinan', 'Deskripsi singkat usaha dan produknya.'],
            ['Usaha warga 4', 'hasil ternak', 'Deskripsi singkat usaha dan produknya.'],
        ];
        foreach ($umkm as [$nama, $produk, $deskripsi]) {
            Umkm::query()->firstOrCreate(['nama' => $nama], [
                'produk' => $produk,
                'deskripsi' => $deskripsi,
                'whatsapp' => null,
                'whatsapp_disetujui' => false,
                'status' => StatusTerbit::Terbit,
                'contoh' => true,
            ]);
        }
    }

    private function apbdes(): void
    {
        $pendapatan = ['Pendapatan Asli Desa', 'Dana Desa', 'Alokasi Dana Desa', 'Bagi Hasil Pajak & Retribusi', 'Bantuan Keuangan', 'Lain-lain Pendapatan yang Sah'];
        $belanja = ['Penyelenggaraan Pemerintahan Desa', 'Pelaksanaan Pembangunan Desa', 'Pembinaan Kemasyarakatan', 'Pemberdayaan Masyarakat', 'Penanggulangan Bencana, Darurat & Mendesak'];

        foreach ([2026, 2025, 2024] as $tahun) {
            $periode = ApbdesPeriode::query()->firstOrCreate(['tahun' => $tahun], [
                'periode' => "1 Januari – 31 Desember {$tahun}",
                'status' => StatusTerbit::Terbit,
                'diterbitkan_at' => now(),
                'contoh' => true,
            ]);

            // Angka resmi belum ada: anggaran dibiarkan null (tampil "Rp —").
            foreach ([[JenisAnggaran::Pendapatan, $pendapatan], [JenisAnggaran::Belanja, $belanja]] as [$jenis, $daftar]) {
                foreach ($daftar as $urutan => $uraian) {
                    $periode->rincian()->firstOrCreate(
                        ['jenis' => $jenis, 'uraian' => $uraian],
                        ['anggaran' => null, 'urutan' => $urutan + 1],
                    );
                }
            }
        }

        // Dokumen contoh tanpa berkas (path null): tombol unduh tetap nonaktif.
        $periode2026 = ApbdesPeriode::query()->where('tahun', 2026)->first();
        $periode2026?->dokumen()->firstOrCreate(
            ['judul' => 'Peraturan Desa tentang APBDes Tahun 2026'],
            ['keterangan_periode' => 'Jan–Des 2026'],
        );
        $periode2026?->dokumen()->firstOrCreate(
            ['judul' => 'Laporan Realisasi APBDes Semester I 2026'],
            ['keterangan_periode' => 'Jan–Jun 2026'],
        );
    }

    private function aspirasi(): void
    {
        $data = [
            ['Pengirim A', 'Mohon informasi tentang perbaikan jalan dusun yang rusak setelah hujan. Pesan contoh dari warga: isi aspirasi asli hanya terlihat oleh perangkat desa yang berwenang dan tidak pernah ditampilkan di situs publik.', '2026-10-27 12:40', StatusAspirasi::Baru],
            ['Pengirim B', 'Mohon informasi tentang jadwal posyandu bulan depan.', '2026-10-26 09:15', StatusAspirasi::Baru],
            ['Pengirim C', 'Mohon informasi tentang bantuan bibit jagung.', '2026-10-25 16:02', StatusAspirasi::Baru],
            ['Pengirim D', 'Mohon informasi tentang lampu jalan yang padam.', '2026-10-20 19:30', StatusAspirasi::Dibaca],
            ['Pengirim E', 'Mohon informasi tentang surat pengantar.', '2026-10-18 08:10', StatusAspirasi::Selesai],
        ];

        foreach ($data as [$nama, $pesan, $waktu, $status]) {
            if (Aspirasi::query()->where('pesan', $pesan)->exists()) {
                continue;
            }

            $aspirasi = Aspirasi::query()->create([
                'nama' => $nama,
                'pesan' => $pesan,
                'status' => $status,
                'dibaca_at' => $status === StatusAspirasi::Baru ? null : $waktu,
                'selesai_at' => $status === StatusAspirasi::Selesai ? $waktu : null,
                'contoh' => true,
            ]);
            $aspirasi->forceFill(['created_at' => $waktu, 'updated_at' => $waktu])->save();
            $aspirasi->riwayat()->create(['aksi' => 'diterima', 'catatan' => 'Aspirasi diterima (status: Baru)']);
        }
    }
}
