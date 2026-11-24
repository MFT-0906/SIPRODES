@props([
    'label',
    'nilai' => 0,
    'keterangan' => null,
    'ikon' => 'chart',
    'warna' => 'hijau', // hijau | kuning | merah | biru
    'href' => null,
    'aksi' => null, // teks tautan saat nilai 0, mis. "Tulis berita"
    'sorot' => false, // true: kartu ditonjolkan bila nilai > 0 (mis. aspirasi baru)
])

@php
    $nada = [
        'hijau' => ['ikon' => 'bg-primary-soft text-primary', 'angka' => 'text-primary', 'garis' => 'bg-primary'],
        'kuning' => ['ikon' => 'bg-accent-soft text-warning', 'angka' => 'text-warning', 'garis' => 'bg-accent'],
        'merah' => ['ikon' => 'bg-danger-soft text-danger', 'angka' => 'text-danger', 'garis' => 'bg-danger'],
        'biru' => ['ikon' => 'bg-info-soft text-info', 'angka' => 'text-info', 'garis' => 'bg-info'],
    ][$warna] ?? null;
    $nada ??= ['ikon' => 'bg-primary-soft text-primary', 'angka' => 'text-primary', 'garis' => 'bg-primary'];
    $kosong = (int) $nilai === 0;
    $menonjol = $sorot && ! $kosong;
@endphp

<div {{ $attributes->class([
    'relative flex flex-col gap-3 overflow-hidden rounded-card border bg-surface p-4 transition-shadow sm:p-5',
    'border-danger/40 bg-danger-soft/40 shadow-[0_8px_24px_rgb(198_40_40/0.10)]' => $menonjol,
    'border-border' => ! $menonjol,
]) }}>
    <span class="absolute inset-x-0 top-0 h-1 {{ $nada['garis'] }} {{ $kosong ? 'opacity-30' : '' }}" aria-hidden="true"></span>
    <div class="flex items-center gap-2.5">
        <span class="flex size-9 shrink-0 items-center justify-center rounded-[10px] {{ $nada['ikon'] }}">
            <x-ui.icon :name="$ikon" class="size-[18px]" />
        </span>
        <p class="type-label text-muted">{{ $label }}</p>
    </div>
    <p @class(['type-h1 tabular-nums', $nada['angka'] => ! $kosong, 'text-muted/60' => $kosong])>{{ number_format((int) $nilai, 0, ',', '.') }}</p>
    <div class="mt-auto flex flex-wrap items-center justify-between gap-x-2 gap-y-1">
        @if ($keterangan)
            <p class="type-label-sm text-muted">{{ $keterangan }}</p>
        @endif
        @if ($href && ($kosong ? $aksi : $menonjol))
            <a href="{{ $href }}" class="relative inline-flex shrink-0 items-center gap-1 rounded-control type-label-sm font-semibold text-primary after:absolute after:-inset-2 hover:underline">
                {{ $kosong ? $aksi : 'Tinjau' }}<span class="sr-only">: {{ $label }}</span>
                <x-ui.icon name="arrow-right" class="size-3.5" />
            </a>
        @endif
    </div>
</div>
