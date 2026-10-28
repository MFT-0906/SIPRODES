<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Uji Token — Desa Marga Agung</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
@php
    // Halaman uji internal (hanya lokal): nilai heks ditulis agar mudah dicocokkan dengan Figma node 20:2850.
    $warna = [
        ['primary', '#1B5E20', 'bg-primary'],
        ['primary-dark', '#14491A', 'bg-primary-dark'],
        ['primary-soft', '#EDF3E6', 'bg-primary-soft'],
        ['accent', '#F9A825', 'bg-accent'],
        ['accent-dark', '#E0941A', 'bg-accent-dark'],
        ['accent-soft', '#FDF3DC', 'bg-accent-soft'],
        ['text', '#212121', 'bg-text'],
        ['muted', '#53594F', 'bg-muted'],
        ['border', '#D5DBD1', 'bg-border'],
        ['surface', '#FFFFFF', 'bg-surface'],
        ['background', '#FAF7EE', 'bg-background'],
        ['success', '#2E7D32', 'bg-success'],
        ['success-soft', '#E8F5EE', 'bg-success-soft'],
        ['warning', '#8A5A12', 'bg-warning'],
        ['warning-soft', '#FDF4E3', 'bg-warning-soft'],
        ['danger', '#C62828', 'bg-danger'],
        ['danger-dark', '#9F1F1F', 'bg-danger-dark'],
        ['danger-soft', '#FDECEA', 'bg-danger-soft'],
        ['info', '#2D5BA8', 'bg-info'],
        ['info-soft', '#EAF0FA', 'bg-info-soft'],
    ];

    $teks = [
        ['Display (HP 28 / desktop 36)', 'type-display'],
        ['H1 (HP 28 / ≥640 px 32)', 'type-h1'],
        ['H2 · 24', 'type-h2'],
        ['H3 · 18', 'type-h3'],
        ['Body Large · 18', 'type-body-lg'],
        ['Body · 16', 'type-body'],
        ['Body Small · 14', 'type-body-sm'],
        ['Label · 14', 'type-label'],
        ['Label Small · 12', 'type-label-sm'],
    ];
@endphp

<main class="container-page py-8">
    <h1 class="type-h1">Uji token — Desa Marga Agung</h1>
    <p class="type-body mt-2 text-muted">Halaman internal untuk mencocokkan token dengan Figma node <code>20:2850</code>. Hanya tampil di lingkungan lokal.</p>

    <h2 class="type-h2 mt-8">Warna</h2>
    <ul class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
        @foreach ($warna as [$nama, $heks, $kelas])
            <li>
                <div class="{{ $kelas }} h-16 rounded-[10px] border border-border"></div>
                <p class="type-label-sm mt-2">--color-{{ $nama }}</p>
                <p class="type-label-sm text-muted">{{ $heks }}</p>
            </li>
        @endforeach
    </ul>

    <h2 class="type-h2 mt-8">Tipografi</h2>
    <div class="mt-4 space-y-3">
        @foreach ($teks as [$label, $kelas])
            <div class="flex flex-col gap-1 sm:flex-row sm:items-baseline sm:gap-6">
                <p class="type-label-sm text-muted sm:w-56 sm:shrink-0">{{ $label }}</p>
                <p class="{{ $kelas }}">Desa Marga Agung</p>
            </div>
        @endforeach
    </div>

    <h2 class="type-h2 mt-8">Radius</h2>
    <div class="mt-4 flex flex-wrap items-center gap-4">
        <div class="flex h-12 w-28 items-center justify-center rounded-control border border-border bg-surface type-label-sm">control 8 px</div>
        <div class="flex h-12 w-28 items-center justify-center rounded-card border border-border bg-surface type-label-sm">card 16 px</div>
        <div class="flex h-12 w-28 items-center justify-center rounded-pill border border-border bg-surface type-label-sm">pill</div>
    </div>

    <h2 class="type-h2 mt-8">Fokus keyboard</h2>
    <p class="type-body-sm mt-2 text-muted">Tekan Tab: cincin fokus hijau di latar terang, kuning di latar gelap.</p>
    <div class="mt-4 flex flex-wrap gap-4">
        <a href="#" class="inline-flex min-h-11 items-center rounded-control border border-border bg-surface px-4 type-label">Tautan terang</a>
        <div class="on-dark rounded-card bg-primary p-4">
            <a href="#" class="inline-flex min-h-11 items-center rounded-control bg-primary-dark px-4 type-label text-surface">Tautan gelap</a>
        </div>
    </div>
</main>
</body>
</html>
