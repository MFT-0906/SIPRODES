@props(['title', 'description', 'href', 'icon' => 'wallet'])

<a href="{{ $href }}" {{ $attributes->class(['flex flex-col items-start gap-3 rounded-card border border-border bg-surface p-5 transition-colors hover:border-primary']) }}>
    <span class="flex size-11 items-center justify-center rounded-[10px] bg-primary-soft text-primary">
        <x-ui.icon :name="$icon" class="size-[22px]" />
    </span>
    <span class="type-h3 text-text">{{ $title }}</span>
    <span class="type-body-sm text-muted">{{ $description }}</span>
</a>
