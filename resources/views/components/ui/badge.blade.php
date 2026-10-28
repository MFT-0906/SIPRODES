@props(['tone' => 'netral']) {{-- sukses | netral | info | peringatan | bahaya | utama | aksen --}}

@php
    $tones = [
        'sukses' => ['bg-success-soft text-success', 'bg-success'],
        'netral' => ['border border-border bg-background text-muted', 'bg-muted'],
        'info' => ['bg-info-soft text-info', 'bg-info'],
        'peringatan' => ['bg-warning-soft text-warning', 'bg-warning'],
        'bahaya' => ['bg-danger-soft text-danger', 'bg-danger'],
        'utama' => ['bg-primary-soft text-primary', 'bg-primary'],
        'aksen' => ['bg-accent-soft text-warning', 'bg-warning'],
    ];
    [$box, $dot] = $tones[$tone] ?? $tones['netral'];
@endphp

{{-- Selalu berteks agar status tidak hanya dibedakan warna. --}}
<span {{ $attributes->class(['inline-flex items-center gap-1.5 rounded-pill px-2.5 py-1 type-label-sm', $box]) }}>
    <span class="size-1.5 shrink-0 rounded-full {{ $dot }}" aria-hidden="true"></span>
    {{ $slot }}
</span>
