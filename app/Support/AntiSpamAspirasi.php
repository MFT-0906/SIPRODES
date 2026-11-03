<?php

namespace App\Support;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

/**
 * Anti-spam formulir Ruang Aspirasi tanpa layanan pihak ketiga:
 * - token waktu terenkripsi dibuat saat formulir dibuka; pengiriman terlalu cepat (bot) ditolak;
 * - kolom jebakan (honeypot) yang tidak boleh diisi manusia.
 */
class AntiSpamAspirasi
{
    public const KOLOM_JEBAKAN = 'situs_web';

    public const KOLOM_TOKEN = 'dibuka';

    /** Token yang lebih tua dari ini dianggap kedaluwarsa (formulir dibiarkan terbuka terlalu lama). */
    private const UMUR_MAKS_DETIK = 86400;

    /**
     * @param  int  $detikLalu  untuk pengujian: seolah-olah formulir dibuka sekian detik yang lalu
     */
    public static function token(int $detikLalu = 0): string
    {
        return Crypt::encryptString((string) (now()->timestamp - $detikLalu));
    }

    /**
     * Mengembalikan pesan galat umum bila token tidak sah atau pengiriman terlalu cepat; null bila lolos.
     */
    public static function periksaToken(?string $token): ?string
    {
        try {
            $dibuka = (int) Crypt::decryptString((string) $token);
        } catch (DecryptException) {
            return 'Pesan belum dapat dikirim. Muat ulang halaman lalu coba lagi.';
        }

        $selisih = now()->timestamp - $dibuka;

        if ($selisih < (int) config('desa.aspirasi.waktu_isi_minimum_detik')) {
            return 'Pesan terkirim terlalu cepat. Periksa isian Anda, tunggu beberapa detik, lalu kirim kembali.';
        }

        if ($selisih > self::UMUR_MAKS_DETIK) {
            return 'Formulir sudah terlalu lama terbuka. Muat ulang halaman lalu coba lagi.';
        }

        return null;
    }
}
