@props(['label', 'value', 'source']) {{-- source wajib: angka harus berasal dari data resmi, lengkap dengan sumber dan tahunnya --}}

<div {{ $attributes->class(['flex flex-col gap-1 rounded-card border border-border bg-surface p-5']) }}>
    <p class="type-label text-muted">{{ $label }}</p>
    <p class="type-h2 text-primary sm:type-h1">{{ $value }}</p>
    <p class="type-label-sm text-muted">{{ $source }}</p>
</div>
