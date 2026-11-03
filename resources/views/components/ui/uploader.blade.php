@props([
    'name',
    'label' => null,
    'accept' => 'application/pdf,image/jpeg,image/png',
    'acceptLabel' => 'PDF, JPG, atau PNG',
    'hint' => 'PDF/JPG/PNG · batas ukuran mengikuti konfigurasi desa',
    'maxMb' => null,
    'id' => null,
    // Hanya untuk peragaan di katalog: kosong | mengunggah | berhasil | ditolak.
    'state' => 'kosong',
    'fileName' => 'dokumen.pdf',
    'fileSize' => '2,4 MB',
    'progress' => 60,
    'message' => '',
])

@php
    $id = $id ?? 'f-'.preg_replace('/[^A-Za-z0-9_-]/', '-', $name);
    $config = [
        'status' => $state,
        'name' => $state === 'kosong' ? '' : $fileName,
        'size' => $state === 'kosong' ? '' : $fileSize,
        'progress' => $progress,
        'message' => $message,
        'accept' => array_values(array_filter(array_map('trim', explode(',', $accept)))),
        'acceptLabel' => $acceptLabel,
        'maxMb' => $maxMb,
    ];
@endphp

{{-- Pemeriksaan di sisi klien hanya untuk kenyamanan. Validasi tipe, isi, dan ukuran berkas tetap wajib di server. --}}
<div x-data="uploader({{ Js::from($config) }})" {{ $attributes->class(['flex flex-col gap-1.5']) }}>
    @if ($label)
        <label for="{{ $id }}" class="type-label text-text">{{ $label }}</label>
    @endif

    <input
        x-ref="input" id="{{ $id }}" type="file" name="{{ $name }}" accept="{{ $accept }}"
        x-on:change="choose($event)" class="peer sr-only"
    >

    <label
        for="{{ $id }}" x-show="status === 'kosong'"
        x-on:dragover.prevent x-on:drop.prevent="drop($event)"
        class="flex cursor-pointer flex-col items-center gap-1.5 rounded-[10px] border-[1.5px] border-dashed border-border bg-background p-6 text-center peer-focus-visible:outline-3 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-primary"
    >
        <x-ui.icon name="upload" />
        <span class="type-label text-primary">Pilih atau seret berkas</span>
        <span class="type-label-sm text-muted">{{ $hint }}</span>
    </label>

    <div
        x-show="status !== 'kosong'" x-cloak aria-live="polite"
        class="flex items-center gap-2.5 rounded-control border p-3"
        :class="status === 'ditolak' ? 'border-danger bg-danger-soft' : 'border-border bg-surface'"
    >
        <span class="shrink-0" :class="status === 'ditolak' ? 'text-danger' : 'text-primary'">
            <x-ui.icon name="file" class="size-5" x-show="status !== 'ditolak'" />
            <x-ui.icon name="alert" class="size-5" x-show="status === 'ditolak'" x-cloak />
        </span>

        <div class="flex min-w-0 flex-1 flex-col gap-1">
            <p class="type-label-sm break-all text-text" x-text="name + ' · ' + size"></p>

            <div x-show="status === 'mengunggah'" class="h-1 overflow-hidden rounded-[2px] bg-border" role="progressbar" aria-label="Kemajuan unggah" :aria-valuenow="progress" aria-valuemin="0" aria-valuemax="100">
                <div class="h-1 rounded-[2px] bg-primary" :style="`width: ${progress}%`"></div>
            </div>
            <p x-show="status === 'mengunggah'" class="type-label-sm text-muted" x-text="`Mengunggah… ${progress}%`"></p>

            <p x-show="status === 'berhasil'" class="flex items-center gap-1 type-label-sm text-success">
                <x-ui.icon name="check" class="size-3" />
                Valid · siap disimpan
            </p>

            <p x-show="status === 'ditolak'" class="type-label-sm text-danger" x-text="message"></p>
        </div>

        <button
            type="button" x-on:click="clear()" aria-label="Hapus berkas"
            class="relative inline-flex size-8 shrink-0 items-center justify-center rounded-[6px] text-text hover:bg-primary-soft after:absolute after:-inset-1"
        >
            <x-ui.icon name="x" class="size-4" />
        </button>
    </div>
</div>
