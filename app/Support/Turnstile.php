<?php

namespace App\Support;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Cloudflare Turnstile untuk formulir Ruang Aspirasi.
 *
 * Aktif hanya bila TURNSTILE_SITE_KEY dan TURNSTILE_SECRET_KEY diisi di .env. Bila tidak aktif, atau bila widget
 * gagal dimuat di peramban, formulir memakai pertanyaan hitungan sebagai cadangan (lihat AntiSpamAspirasi).
 * Alamat IP pengunjung sengaja tidak dikirim ke Cloudflare (parameter remoteip bersifat opsional).
 */
final class Turnstile
{
    public const KOLOM_TOKEN = 'cf-turnstile-response';

    public const ORIGIN = 'https://challenges.cloudflare.com';

    private const ALAMAT_VERIFIKASI = self::ORIGIN.'/turnstile/v0/siteverify';

    public static function aktif(): bool
    {
        return filled(config('desa.turnstile.site_key')) && filled(config('desa.turnstile.secret_key'));
    }

    public static function siteKey(): ?string
    {
        return self::aktif() ? (string) config('desa.turnstile.site_key') : null;
    }

    /**
     * Memeriksa token widget ke Cloudflare.
     *
     * @return bool|null true = lolos, false = ditolak Cloudflare, null = Cloudflare tidak dapat dihubungi
     */
    public static function periksa(string $token): ?bool
    {
        try {
            $respons = Http::asForm()->timeout(6)->post(self::ALAMAT_VERIFIKASI, [
                'secret' => config('desa.turnstile.secret_key'),
                'response' => $token,
            ]);
        } catch (ConnectionException) {
            return null;
        } catch (Throwable) {
            return null;
        }

        if ($respons->serverError()) {
            return null;
        }

        return $respons->successful() && $respons->json('success') === true;
    }
}
