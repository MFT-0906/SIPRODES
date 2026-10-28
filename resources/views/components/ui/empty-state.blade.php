@props(['title', 'description', 'icon' => 'newspaper', 'heading' => 'h2']) {{-- slot `action` opsional: tautan/tombol langkah berikutnya --}}

<div {{ $attributes->class(['flex flex-col items-center gap-2.5 rounded-[14px] border-[1.5px] border-dashed border-border bg-surface px-8 py-10 text-center']) }}>
    <span class="flex size-14 items-center justify-center rounded-full bg-primary-soft text-primary">
        <x-ui.icon :name="$icon" class="size-[26px]" />
    </span>
    <{{ $heading }} class="type-h3 text-text">{{ $title }}</{{ $heading }}>
    <p class="type-body max-w-prose text-muted">{{ $description }}</p>
    @isset($action)
        <div class="mt-1.5">{{ $action }}</div>
    @endisset
</div>
