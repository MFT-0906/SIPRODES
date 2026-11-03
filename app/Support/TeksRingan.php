<?php

namespace App\Support;

use Illuminate\Support\HtmlString;
use League\CommonMark\CommonMarkConverter;

/**
 * Merender teks penanda ringan (editor berita: **tebal**, _miring_, ## subjudul, daftar, [tautan](alamat), > kutipan)
 * menjadi HTML yang aman. HTML mentah dari pengguna dibuang dan tautan berbahaya (javascript:, data:) tidak dirender.
 */
class TeksRingan
{
    public static function html(?string $teks): HtmlString
    {
        $konverter = new CommonMarkConverter([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
            'max_nesting_level' => 20,
        ]);

        return new HtmlString($konverter->convert((string) $teks)->getContent());
    }

    /**
     * Teks polos (tanpa penanda) untuk menghitung kata/waktu baca.
     */
    public static function polos(?string $teks): string
    {
        return trim(html_entity_decode(strip_tags((string) self::html($teks)), ENT_QUOTES | ENT_HTML5));
    }
}
