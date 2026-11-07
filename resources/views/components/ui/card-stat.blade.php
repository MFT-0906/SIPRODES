@props(['label', 'value', 'source' => null, 'tone' => 'utama']) {{-- sumber angka wajib dicantumkan: di kartu ini, atau satu kali untuk seluruh deret kartu yang bersumber sama (source dikosongkan); tone: utama | bahaya --}}

<div {{ $attributes->class(['flex flex-col gap-1 rounded-card border border-border bg-surface p-5']) }}>
    <p class="type-label text-muted">{{ $label }}</p>
    <p @class(['type-h2 sm:type-h1 tabular-nums', 'text-primary' => $tone !== 'bahaya', 'text-danger' => $tone === 'bahaya'])>{{ $value }}</p>
    @if ($source)
        <p class="type-label-sm text-muted">{{ $source }}</p>
    @endif
</div>
