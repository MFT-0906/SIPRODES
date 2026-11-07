@props(['title', 'description', 'icon' => 'newspaper', 'heading' => 'h2', 'tone' => 'netral', 'ringkas' => false]) {{-- slot `action` opsional: tautan/tombol langkah berikutnya; tone: netral | bahaya --}}

@php
    $bahaya = $tone === 'bahaya';
@endphp

<div {{ $attributes->class([
    'flex flex-col items-center gap-2.5 rounded-[14px] border-[1.5px] border-dashed px-8 text-center',
    'py-10' => ! $ringkas,
    'py-5' => $ringkas,
    'border-border bg-surface' => ! $bahaya,
    'border-danger bg-danger-soft' => $bahaya,
]) }} @if ($bahaya) role="alert" @endif>
    <span @class([
        'flex size-14 items-center justify-center rounded-full',
        'bg-primary-soft text-primary' => ! $bahaya,
        'bg-surface text-danger' => $bahaya,
    ])>
        <x-ui.icon :name="$icon" class="size-[26px]" />
    </span>
    <{{ $heading }} class="type-h3 text-text">{{ $title }}</{{ $heading }}>
    <p class="type-body max-w-prose text-muted">{{ $description }}</p>
    @isset($action)
        <div class="mt-1.5">{{ $action }}</div>
    @endisset
</div>
