@props(['caption' => 'Foto'])

{{-- Pengganti foto sampai foto asli yang disetujui desa tersedia (bukan foto AI/stok). --}}
<div {{ $attributes->class(['flex flex-col items-center justify-center gap-2 bg-primary-soft text-primary']) }}>
    <x-ui.icon name="image" class="size-7" />
    <span class="type-label-sm text-center">{{ $caption }}</span>
</div>
