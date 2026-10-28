@props(['title', 'description', 'href', 'icon' => 'wallet', 'row' => false]) {{-- row: di layar HP tampil mendatar dengan panah (mis. akses Ruang Aspirasi) --}}

<a href="{{ $href }}" {{ $attributes->class([
    'flex flex-col items-start gap-3 rounded-card border border-border bg-surface p-5 transition-colors hover:border-primary',
    'max-md:flex-row max-md:items-center max-md:gap-3.5 max-md:p-4' => $row,
]) }}>
    <span class="flex size-11 shrink-0 items-center justify-center rounded-[10px] bg-primary-soft text-primary">
        <x-ui.icon :name="$icon" class="size-[22px]" />
    </span>
    <span class="flex min-w-0 flex-1 flex-col gap-3 {{ $row ? 'max-md:gap-0.5' : '' }}">
        <span class="type-h3 text-text {{ $row ? 'max-md:type-label' : '' }}">{{ $title }}</span>
        <span class="type-body-sm text-muted">{{ $description }}</span>
    </span>
    @if ($row)
        <x-ui.icon name="chevron-right" class="hidden size-5 shrink-0 text-muted max-md:block" />
    @endif
</a>
