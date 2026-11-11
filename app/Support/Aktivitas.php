<?php

namespace App\Support;

use App\Models\LogAktivitas;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Throwable;

/**
 * Audit log tindakan sensitif (NFR-02). Pencatatan tidak boleh menggagalkan tindakan utama:
 * galat saat menulis log hanya dilaporkan. Jangan memasukkan kata sandi, NIK, atau isi aspirasi ke keterangan.
 */
class Aktivitas
{
    /** Label tampil untuk daftar log di halaman Akun. */
    public const LABEL = [
        'masuk' => 'Masuk',
        'keluar' => 'Keluar',
        'masuk.dikunci' => 'Login dikunci (gagal berulang)',
        'masuk.google' => 'Masuk dengan Google',
        'masuk.google_ditolak' => 'Masuk dengan Google ditolak',
        'akun.google_dilepas' => 'Tautan akun Google dilepas',
        'kata_sandi.diatur_ulang' => 'Kata sandi diatur ulang',
        'akun.dibuat' => 'Akun dibuat',
        'akun.diubah' => 'Akun diubah',
        'akun.tautan_dikirim' => 'Tautan atur ulang dikirim',
        'berita.dibuat' => 'Berita dibuat',
        'berita.diubah' => 'Berita diubah',
        'berita.diterbitkan' => 'Berita diterbitkan',
        'berita.ditarik' => 'Berita ditarik ke draf',
        'berita.dihapus' => 'Berita dihapus',
        'agenda.dibuat' => 'Agenda dibuat',
        'agenda.diubah' => 'Agenda diubah',
        'agenda.diterbitkan' => 'Agenda diterbitkan',
        'agenda.ditarik' => 'Agenda ditarik ke draf',
        'agenda.dihapus' => 'Agenda dihapus',
        'profil.diubah' => 'Profil desa diubah',
        'profil.kontak_diterbitkan' => 'Kontak & lokasi diterbitkan',
        'profil.kontak_ditarik' => 'Kontak & lokasi ditarik',
        'perangkat.dibuat' => 'Perangkat desa dibuat',
        'perangkat.diubah' => 'Perangkat desa diubah',
        'perangkat.dihapus' => 'Perangkat desa dihapus',
        'perangkat.urutan' => 'Urutan perangkat diubah',
        'komoditas.dibuat' => 'Komoditas dibuat',
        'komoditas.diubah' => 'Komoditas diubah',
        'komoditas.diterbitkan' => 'Komoditas diterbitkan',
        'komoditas.ditarik' => 'Komoditas ditarik ke draf',
        'komoditas.dihapus' => 'Komoditas dihapus',
        'kelompok_tani.dibuat' => 'Kelompok tani dibuat',
        'kelompok_tani.diubah' => 'Kelompok tani diubah',
        'kelompok_tani.dihapus' => 'Kelompok tani dihapus',
        'umkm.dibuat' => 'UMKM dibuat',
        'umkm.diubah' => 'UMKM diubah',
        'umkm.diterbitkan' => 'UMKM diterbitkan',
        'umkm.ditarik' => 'UMKM ditarik ke draf',
        'umkm.dihapus' => 'UMKM dihapus',
        'apbdes.dibuat' => 'Periode APBDes dibuat',
        'apbdes.diubah' => 'Periode APBDes diubah',
        'apbdes.diterbitkan' => 'Periode APBDes diterbitkan',
        'apbdes.ditarik' => 'Periode APBDes ditarik ke draf',
        'apbdes.dihapus' => 'Periode APBDes dihapus',
        'apbdes.dokumen_diunggah' => 'Dokumen APBDes diunggah',
        'apbdes.dokumen_dihapus' => 'Dokumen APBDes dihapus',
        'aspirasi.status' => 'Status aspirasi diubah',
        'master.dibuat' => 'Data master dibuat',
        'master.diubah' => 'Data master diubah',
        'master.dihapus' => 'Data master dihapus',
    ];

    public static function catat(string $aksi, ?Model $subjek = null, ?string $keterangan = null): void
    {
        try {
            LogAktivitas::query()->create([
                'user_id' => auth()->id(),
                'aksi' => $aksi,
                'subjek_type' => $subjek?->getMorphClass(),
                'subjek_id' => $subjek?->getKey(),
                'keterangan' => $keterangan !== null ? Str::limit($keterangan, 250, '…') : null,
                'ip' => request()?->ip(),
            ]);
        } catch (Throwable $e) {
            report($e);
        }
    }

    /**
     * Mencatat simpan konten berstatus terbit/draf: dibuat atau diubah, ditambah peristiwa terbit/tarik bila status berubah.
     *
     * @param  string  $modul  awalan aksi, mis. "berita"
     */
    public static function simpanKonten(string $modul, Model $subjek, string $judul, bool $baru, ?string $statusLama, string $statusBaru): void
    {
        self::catat("{$modul}.".($baru ? 'dibuat' : 'diubah'), $subjek, $judul);

        if ($statusBaru === 'terbit' && $statusLama !== 'terbit') {
            self::catat("{$modul}.diterbitkan", $subjek, $judul);
        } elseif ($statusBaru === 'draf' && $statusLama === 'terbit') {
            self::catat("{$modul}.ditarik", $subjek, $judul);
        }
    }
}
