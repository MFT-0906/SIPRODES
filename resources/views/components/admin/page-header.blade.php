@props(['title', 'description' => null]) {{-- slot bawaan: tombol aksi utama halaman --}}

<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div class="flex flex-col gap-1">
        <h1 class="type-h2 text-text">{{ $title }}</h1>
        @if ($description)
            <p class="type-body-sm text-muted">{{ $description }}</p>
        @endif
    </div>
    @if (! $slot->isEmpty())
        <div class="shrink-0">{{ $slot }}</div>
    @endif
</div>
