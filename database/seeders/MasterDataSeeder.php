<?php

namespace Database\Seeders;

use App\Models\Dusun;
use App\Models\Jabatan;
use App\Models\ProfilDesa;
use Illuminate\Database\Seeder;

/**
 * Data master yang dibutuhkan situs agar berjalan. Aman dijalankan ulang dan di produksi.
 * Nilai bawaan berasal dari frontend (Dusun 1–6, daftar jabatan) dan menunggu konfirmasi desa.
 */
class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 6; $i++) {
            Dusun::query()->firstOrCreate(['nama' => "Dusun {$i}"], ['urutan' => $i]);
        }

        $jabatan = [
            'Kepala Desa', 'Sekretaris Desa', 'Kaur Keuangan', 'Kaur Perencanaan', 'Kaur Tata Usaha & Umum',
            'Kasi Pemerintahan', 'Kasi Kesejahteraan', 'Kasi Pelayanan',
        ];
        foreach ($jabatan as $urutan => $nama) {
            Jabatan::query()->firstOrCreate(['nama' => $nama], ['urutan' => $urutan + 1]);
        }

        if (ProfilDesa::query()->doesntExist()) {
            ProfilDesa::query()->create([
                'sejarah' => "Desa Marga Agung merupakan wilayah transmigrasi bedol desa yang berasal dari Desa Kaligesik, Kecamatan Srumbung, Kabupaten Magelang, Jawa Tengah. Kini desa berada di Kecamatan Jati Agung, Kabupaten Lampung Selatan.\n\nSebagian besar warganya bekerja sebagai petani, dengan jagung sebagai komoditas utama. Kini desa terbagi dalam enam dusun dengan luas wilayah sekitar 1.050 hektare.",
                'visi' => null,
                'misi' => [],
                'visi_misi_resmi' => false,
                'alamat_wilayah' => 'Desa Marga Agung, Kecamatan Jati Agung, Kabupaten Lampung Selatan, Provinsi Lampung',
                'kontak_terverifikasi' => false,
                'penduduk' => 4492,
                'kk' => 1309,
                'luas_ha' => 1050,
                'jumlah_dusun' => 6,
                'mata_pencaharian' => 'Petani',
                'sumber_data' => 'Sumber: survei awal 2026 · perlu verifikasi desa',
                'data_terverifikasi' => false,
                'perangkat_resmi' => false,
            ]);
        }
    }
}
