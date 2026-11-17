<?php

namespace App\Http\Middleware;

use App\Models\StatistikKunjungan;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Menghitung kunjungan halaman publik secara internal (tanpa cookie dan tanpa pihak ketiga).
 *
 * Yang disimpan di basis data hanya agregat harian per halaman. Pengunjung unik per hari dikenali dari hash
 * (IP + user agent + tanggal, dengan kunci APP_KEY) yang hanya hidup di cache sampai akhir hari; IP, user agent,
 * dan hash itu tidak pernah masuk basis data. Kegagalan pencatatan tidak boleh membuat halaman galat.
 */
class CatatKunjungan
{
    /** Hanya halaman publik bernama ini yang dihitung; admin, aset, sitemap, dan robots otomatis di luar daftar. */
    private const RUTE_PUBLIK = [
        'home', 'profil', 'apbdes', 'potensi', 'berita.index', 'berita.show', 'kontak', 'aspirasi.create', 'kebijakan-privasi',
    ];

    private const POLA_BOT = '/bot|crawl|spider|slurp|preview|facebookexternalhit|whatsapp|telegram|curl|wget|python|java\/|go-http|httpclient|headless|lighthouse|pingdom|uptime|monitor|scrape|fetch|okhttp|axios|libwww|php\//i';

    public function handle(Request $request, Closure $next): Response
    {
        $respons = $next($request);

        try {
            if ($this->layakDihitung($request, $respons)) {
                $this->catat($request);
            }
        } catch (Throwable $e) {
            report($e);
        }

        return $respons;
    }

    private function layakDihitung(Request $request, Response $respons): bool
    {
        if (! config('desa.statistik.aktif') || ! $request->isMethod('GET') || $respons->getStatusCode() !== 200) {
            return false;
        }

        if (! in_array($request->route()?->getName(), self::RUTE_PUBLIK, true)) {
            return false;
        }

        if (! str_starts_with((string) $respons->headers->get('Content-Type'), 'text/html')) {
            return false;
        }

        // Permintaan AJAX dan prefetch/prerender peramban bukan kunjungan nyata.
        if ($request->ajax() || $request->headers->has('X-Moz') || $request->headers->has('X-Purpose')
            || in_array(strtolower((string) $request->header('Purpose')), ['prefetch', 'prerender'], true)
            || str_contains(strtolower((string) $request->header('Sec-Purpose')), 'prefetch')) {
            return false;
        }

        $agen = trim((string) $request->userAgent());

        return $agen !== '' && ! preg_match(self::POLA_BOT, $agen);
    }

    private function catat(Request $request): void
    {
        $tanggal = now()->toDateString();
        $path = mb_substr('/'.ltrim($request->path(), '/'), 0, 150);

        $sidik = hash_hmac('sha256', $request->ip().'|'.$request->userAgent().'|'.$tanggal, (string) config('app.key'));
        $akhirHari = now()->endOfDay()->addMinutes(5);

        // Cache::add hanya berhasil bila kunci belum ada: pengunjung yang sama dihitung sekali per hari (dan sekali per halaman).
        $baruSitus = Cache::add('kunjungan:'.$tanggal.':'.$sidik, 1, $akhirHari);
        $baruHalaman = Cache::add('kunjungan:'.$tanggal.':'.hash('sha256', $sidik.$path), 1, $akhirHari);

        $this->tambah($tanggal, StatistikKunjungan::SEMUA, $baruSitus ? 1 : 0);
        $this->tambah($tanggal, $path, $baruHalaman ? 1 : 0);
    }

    private function tambah(string $tanggal, string $path, int $pengunjungBaru): void
    {
        StatistikKunjungan::query()->upsert(
            [['tanggal' => $tanggal, 'path' => $path, 'jumlah_tayang' => 1, 'jumlah_pengunjung' => $pengunjungBaru]],
            ['tanggal', 'path'],
            [
                'jumlah_tayang' => DB::raw('jumlah_tayang + 1'),
                'jumlah_pengunjung' => DB::raw('jumlah_pengunjung + '.$pengunjungBaru),
            ],
        );
    }
}
