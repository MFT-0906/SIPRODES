@props(['status' => 'draf']) {{-- terbit | draf | lewat | baru | dibaca | selesai --}}

@php
    $peta = [
        'terbit' => ['sukses', 'Terbit'],
        'draf' => ['netral', 'Draf'],
        'lewat' => ['netral', 'Sudah lewat'],
        'baru' => ['info', 'Baru'],
        'dibaca' => ['peringatan', 'Sudah Dibaca'],
        'selesai' => ['sukses', 'Selesai'],
    ];
    [$nada, $teks] = $peta[$status] ?? $peta['draf'];
@endphp

<x-ui.badge :tone="$nada" {{ $attributes }}>{{ $teks }}</x-ui.badge>
