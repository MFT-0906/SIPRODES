@props(['label', 'value', 'source' => null, 'tone' => 'utama', 'icon' => null, 'aksen' => null]) {{-- sumber angka wajib dicantumkan: di kartu ini, atau satu kali untuk seluruh deret kartu yang bersumber sama (source dikosongkan); tone: utama | bahaya; icon + aksen (warna CSS): ikon dan garis warna di sisi atas kartu --}}

<div {{ $attributes->class(['relative flex flex-col gap-1 overflow-hidden rounded-card border border-border bg-surface p-5']) }}>
    @if ($aksen)
        <span class="absolute inset-x-0 top-0 h-1" style="background: {{ $aksen }}" aria-hidden="true"></span>
    @endif
    @if ($icon)
        <div class="flex items-center gap-2.5">
            <span class="flex size-9 shrink-0 items-center justify-center rounded-[10px]" style="background: color-mix(in srgb, {{ $aksen ?? 'var(--color-primary)' }} 14%, white); color: {{ $aksen ?? 'var(--color-primary)' }}">
                <x-ui.icon :name="$icon" class="size-[18px]" />
            </span>
            <p class="type-label text-muted">{{ $label }}</p>
        </div>
    @else
        <p class="type-label text-muted">{{ $label }}</p>
    @endif
    <p @class(['type-h2 sm:type-h1 tabular-nums', 'text-primary' => $tone !== 'bahaya', 'text-danger' => $tone === 'bahaya'])>{{ $value }}</p>
    @if ($source)
        <p class="type-label-sm text-muted">{{ $source }}</p>
    @endif
</div>
