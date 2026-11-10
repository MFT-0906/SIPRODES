<?php

namespace App\Http\Controllers;

use App\Models\Berita;
use Illuminate\Http\Response;

/**
 * Peta situs untuk mesin pencari (Google Search Console) dan robots.txt yang menunjuk ke sana.
 * Hanya memuat halaman publik dan berita berstatus terbit; draf dan halaman admin tidak pernah masuk.
 */
class SitemapController extends Controller
{
    /** Halaman publik tetap: [nama rute, prioritas, frekuensi perubahan]. */
    private const HALAMAN = [
        ['home', '1.0', 'weekly'],
        ['profil', '0.8', 'monthly'],
        ['apbdes', '0.8', 'monthly'],
        ['potensi', '0.7', 'monthly'],
        ['berita.index', '0.8', 'daily'],
        ['kontak', '0.6', 'monthly'],
        ['aspirasi.create', '0.5', 'yearly'],
    ];

    public function sitemap(): Response
    {
        $alamat = [];

        foreach (self::HALAMAN as [$rute, $prioritas, $frekuensi]) {
            $alamat[] = ['loc' => route($rute), 'changefreq' => $frekuensi, 'priority' => $prioritas];
        }

        Berita::query()->terbit()->orderByDesc('tanggal')->orderByDesc('id')
            ->get(['slug', 'updated_at', 'diterbitkan_at', 'tanggal'])
            ->each(function (Berita $b) use (&$alamat) {
                $ubah = $b->updated_at ?? $b->diterbitkan_at ?? $b->tanggal;
                $alamat[] = [
                    'loc' => route('berita.show', $b->slug),
                    'lastmod' => $ubah?->toAtomString(),
                    'changefreq' => 'monthly',
                    'priority' => '0.6',
                ];
            });

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n"
            .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
        foreach ($alamat as $a) {
            $xml .= '  <url>'."\n";
            foreach (['loc', 'lastmod', 'changefreq', 'priority'] as $tag) {
                if (filled($a[$tag] ?? null)) {
                    $xml .= '    <'.$tag.'>'.htmlspecialchars($a[$tag], ENT_XML1 | ENT_QUOTES, 'UTF-8').'</'.$tag.'>'."\n";
                }
            }
            $xml .= '  </url>'."\n";
        }
        $xml .= '</urlset>'."\n";

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function robots(): Response
    {
        $isi = "User-agent: *\nDisallow:\n\nSitemap: ".route('sitemap')."\n";

        return response($isi, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
