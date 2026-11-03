<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Pencarian teks sederhana (LIKE) yang aman: karakter khusus LIKE di-escape dan nilai selalu lewat binding.
 */
trait DapatDicari
{
    /**
     * @param  Builder<static>  $query
     * @param  list<string>  $kolom
     */
    public function scopeCari(Builder $query, array $kolom, ?string $kata): void
    {
        $kata = mb_substr(trim((string) $kata), 0, 100);

        if ($kata === '' || $kolom === []) {
            return;
        }

        $pola = '%'.preg_replace('/([!%_])/', '!$1', $kata).'%';
        $grammar = $query->getQuery()->getGrammar();

        $query->where(function (Builder $q) use ($kolom, $pola, $grammar, $query) {
            foreach ($kolom as $nama) {
                $q->orWhereRaw($grammar->wrap($query->qualifyColumn($nama))." like ? escape '!'", [$pola]);
            }
        });
    }
}
