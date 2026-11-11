<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Masuk dashboard dengan akun Google (OAuth 2.0 / OpenID Connect, alur authorization code + PKCE).
 *
 * Dibuat langsung dengan HTTP client Laravel, tanpa paket tambahan. Aktif hanya bila GOOGLE_CLIENT_ID dan
 * GOOGLE_CLIENT_SECRET diisi di .env. Google hanya dipakai untuk membuktikan pemilik email; siapa yang boleh
 * masuk dan hak aksesnya tetap ditentukan oleh akun yang dibuat Super Admin.
 */
final class GoogleLogin
{
    private const ALAMAT_OTORISASI = 'https://accounts.google.com/o/oauth2/v2/auth';

    private const ALAMAT_TOKEN = 'https://oauth2.googleapis.com/token';

    private const ALAMAT_PROFIL = 'https://openidconnect.googleapis.com/v1/userinfo';

    public static function aktif(): bool
    {
        return filled(config('desa.google.client_id')) && filled(config('desa.google.client_secret'));
    }

    public static function alamatKembali(): string
    {
        return route('admin.login.google.kembali');
    }

    /** Alamat halaman pilih akun Google. $verifier disimpan di sesi; yang dikirim hanya hash-nya (PKCE S256). */
    public static function alamatOtorisasi(string $state, string $verifier): string
    {
        return self::ALAMAT_OTORISASI.'?'.http_build_query([
            'client_id' => config('desa.google.client_id'),
            'redirect_uri' => self::alamatKembali(),
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'code_challenge' => self::tantangan($verifier),
            'code_challenge_method' => 'S256',
            'prompt' => 'select_account',
            'access_type' => 'online',
        ], '', '&', PHP_QUERY_RFC3986);
    }

    public static function tantangan(string $verifier): string
    {
        return rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');
    }

    /**
     * Menukar kode dari Google dengan profil pengguna.
     *
     * @return array{sub: string, email: string, email_verified: bool, name: ?string}|null null bila gagal
     */
    public static function profil(string $kode, string $verifier): ?array
    {
        try {
            $token = Http::asForm()->timeout(8)->post(self::ALAMAT_TOKEN, [
                'client_id' => config('desa.google.client_id'),
                'client_secret' => config('desa.google.client_secret'),
                'code' => $kode,
                'code_verifier' => $verifier,
                'grant_type' => 'authorization_code',
                'redirect_uri' => self::alamatKembali(),
            ]);

            $akses = $token->successful() ? $token->json('access_token') : null;
            if (! is_string($akses) || $akses === '') {
                return null;
            }

            $profil = Http::withToken($akses)->timeout(8)->get(self::ALAMAT_PROFIL);
            if (! $profil->successful()) {
                return null;
            }
        } catch (Throwable $e) {
            report($e);

            return null;
        }

        $sub = $profil->json('sub');
        $email = $profil->json('email');
        if (! is_string($sub) || $sub === '' || ! is_string($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        return [
            'sub' => $sub,
            'email' => mb_strtolower($email),
            'email_verified' => $profil->json('email_verified') === true,
            'name' => is_string($profil->json('name')) ? $profil->json('name') : null,
        ];
    }
}
