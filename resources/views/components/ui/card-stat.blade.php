@props(['label', 'value', 'source', 'tone' => 'utama']) {{-- source wajib: angka harus berasal dari data resmi, lengkap dengan sumber dan tahunnya; tone: utama | bahaya --}}

<div {{ $attributes->class(['flex flex-col gap-1 rounded-card border border-border bg-surface p-5']) }}>
    <p class="type-label text-muted">{{ $label }}</p>
    <p @class(['type-h2 sm:type-h1', 'text-primary' => $tone !== 'bahaya', 'text-danger' => $tone === 'bahaya'])>{{ $value }}</p>
    <p class="type-label-sm text-muted">{{ $source }}</p>
</div>
