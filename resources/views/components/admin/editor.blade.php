@props(['name', 'label', 'value' => null, 'required' => true, 'error' => null, 'help' => null, 'id' => null])

@php
    $id = $id ?? 'f-'.preg_replace('/[^A-Za-z0-9_-]/', '-', $name);
    $error = $error ?? (isset($errors) && $errors->has($name) ? $errors->first($name) : null);
    $value = old($name, $value);
    $describedBy = trim(($error ? $id.'-error ' : '').$id.'-help');
    // Teks biasa dengan penanda ringan (**tebal**, _miring_, ## subjudul, daftar, [tautan](alamat)).
    // TODO(backend): bersihkan dan render isi di sisi server; jangan menaruh HTML mentah dari pengguna.
    $alat = [
        ['B', 'Tebal', 'bungkus', '**', '**', 'teks tebal'],
        ['I', 'Miring', 'bungkus', '_', '_', 'teks miring'],
        ['H2', 'Subjudul', 'awalan', '## ', '', 'Subjudul'],
        ['• Daftar', 'Daftar berpoin', 'awalan', '- ', '', 'butir daftar'],
        ['1. Daftar', 'Daftar bernomor', 'awalan', '1. ', '', 'butir daftar'],
        ['Tautan', 'Tautan', 'bungkus', '[', '](https://)', 'teks tautan'],
    ];
@endphp

<div class="flex flex-col gap-1.5" x-data="editorIsi">
    <label for="{{ $id }}" class="flex items-baseline gap-1.5">
        <span class="type-label text-text">{{ $label }}</span>
        <span class="type-label-sm {{ $required ? 'text-danger' : 'text-muted' }}">{{ $required ? 'Wajib' : 'Opsional' }}</span>
    </label>

    <div @class([
        'overflow-hidden rounded-control border bg-surface',
        'border-danger shadow-[0_0_0_1px_var(--color-danger)]' => $error,
        'border-border focus-within:border-primary focus-within:shadow-[0_0_0_1px_var(--color-primary),0_0_0_4px_rgb(27_94_32/0.18)]' => ! $error,
    ])>
        <div role="toolbar" aria-label="Format isi berita" aria-controls="{{ $id }}" class="flex flex-wrap gap-1 border-b border-border bg-background px-2.5 py-2">
            @foreach ($alat as [$teks, $nama, $jenis, $awal, $akhir, $contoh])
                <button
                    type="button" aria-label="{{ $nama }}" title="{{ $nama }}"
                    x-on:click="terapkan({{ Js::from($jenis) }}, {{ Js::from($awal) }}, {{ Js::from($akhir) }}, {{ Js::from($contoh) }})"
                    class="min-h-11 rounded-[6px] px-2.5 type-label text-text hover:bg-primary-soft"
                >{{ $teks }}</button>
            @endforeach
        </div>
        <textarea
            id="{{ $id }}" name="{{ $name }}" x-ref="isi" rows="10" required
            placeholder="Tulis isi berita di sini…"
            @if ($error) aria-invalid="true" @endif
            aria-describedby="{{ $describedBy }}"
            class="block min-h-[220px] w-full resize-y border-0 bg-surface p-3.5 type-body text-text placeholder:text-muted focus-visible:outline-none"
        >{{ $value }}</textarea>
    </div>

    @if ($error)
        <p id="{{ $id }}-error" class="flex items-start gap-1.5 type-body-sm text-danger">
            <x-ui.icon name="alert" class="mt-0.5 size-4" />
            <span>{{ $error }}</span>
        </p>
    @endif
    <p id="{{ $id }}-help" class="type-body-sm text-muted">{{ $help ?? 'Gunakan tombol format di atas. Baris kosong memisahkan paragraf.' }}</p>
</div>
