@props(['tone' => 'info', 'title' => null]) {{-- info | sukses | peringatan | bahaya --}}

@php
    $tones = [
        'info' => ['border-info bg-info-soft', 'text-info', 'alert', 'status'],
        'sukses' => ['border-success bg-success-soft', 'text-success', 'check', 'status'],
        'peringatan' => ['border-warning bg-warning-soft', 'text-warning', 'alert', 'alert'],
        'bahaya' => ['border-danger bg-danger-soft', 'text-danger', 'alert', 'alert'],
    ];
    [$box, $iconColor, $icon, $role] = $tones[$tone] ?? $tones['info'];
@endphp

<div role="{{ $role }}" {{ $attributes->class(['flex items-start gap-3 rounded-control border p-3.5', $box]) }}>
    <x-ui.icon :name="$icon" class="mt-0.5 size-5 {{ $iconColor }}" />
    <div class="min-w-0 flex-1 type-body-sm text-text">
        @if ($title)
            <p class="type-label {{ $iconColor }}">{{ $title }}</p>
        @endif
        {{ $slot }}
    </div>
</div>
