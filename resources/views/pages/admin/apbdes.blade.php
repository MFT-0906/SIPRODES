@extends('layouts.admin')

@section('title', 'Kelola APBDes')
@section('judul', 'APBDes')
@section('breadcrumb', 'Dashboard / Konten Desa / APBDes')

@php
    use App\Support\Format;

    $cols = 'minmax(0,1.2fr) 100px 120px 100px';
    $adaItem = ! $baru && $item;
    $judulForm = $adaItem ? 'APBDes '.$item->tahun : 'Tambah Periode APBDes';
    $aksiForm = $adaItem ? route('admin.apbdes.update', $item) : route('admin.apbdes.store');
    $statusAwal = old('status', $item?->status->value ?? 'draf');
    $bagian = [
        'pendapatan' => ['Pendapatan desa', 'Pendapatan per sumber'],
        'belanja' => ['Belanja desa', 'Belanja per bidang'],
        'pembiayaan' => ['Pembiayaan', 'Isi angka pembiayaan netto bila ada'],
    ];
@endphp

@section('content')
    <div class="flex flex-col gap-6">
        <x-admin.page-header title="Kelola APBDes" description="Kelola rincian anggaran dan dokumen PDF per tahun. Total dihitung otomatis dari rincian.">
            <x-ui.button :href="route('admin.apbdes', ['baru' => 1]).'#form-apbdes'" icon="plus">Tambah Periode</x-ui.button>
        </x-admin.page-header>

        <x-admin.galat />

        <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,0.8fr)_minmax(0,1.2fr)]">
            <section aria-label="Daftar periode APBDes" class="rounded-[14px] border border-border bg-surface">
                @if ($periode->count())
                    <x-admin.table :cols="$cols" caption="Daftar periode APBDes beserta jumlah dokumen dan status" :heads="[['PERIODE', 'left'], ['DOKUMEN', 'left'], ['STATUS', 'left'], ['AKSI', 'right']]">
                        @foreach ($periode as $i => $p)
                            <x-admin.tr :cols="$cols" :aktif="$adaItem && $item->id === $p->id" class="first:border-t-0">
                                <th scope="row" class="text-left font-[inherit]">
                                    <a href="{{ route('admin.apbdes', ['periode' => $p->id]) }}#form-apbdes" class="inline-flex min-h-11 items-center type-label text-text hover:underline">APBDes {{ $p->tahun }}</a>
                                </th>
                                <td class="type-body-sm text-muted"><span class="type-label-sm lg:sr-only">Dokumen: </span>{{ $p->dokumen_count }} PDF</td>
                                <td><x-admin.status-badge :status="$p->status->value" /></td>
                                <td class="flex items-center gap-1.5 lg:justify-end">
                                    <x-admin.icon-button icon="edit" :href="route('admin.apbdes', ['periode' => $p->id]).'#form-apbdes'" :label="'Ubah APBDes '.$p->tahun" />
                                    <x-admin.icon-button icon="trash" tone="bahaya" :label="'Hapus APBDes '.$p->tahun" x-on:click="$dispatch('open-modal', 'hapus-apbdes-{{ $i }}')" />
                                </td>
                            </x-admin.tr>
                        @endforeach
                    </x-admin.table>
                @else
                    <div class="p-4 lg:p-5">
                        <x-ui.empty-state icon="wallet" title="Belum ada periode APBDes" description="Tambahkan periode pertama melalui formulir di samping." />
                    </div>
                @endif
            </section>

            <section id="form-apbdes" aria-labelledby="form-apbdes-judul" class="scroll-mt-24 rounded-[14px] border border-border bg-surface p-5 lg:p-6">
                <h2 id="form-apbdes-judul" class="type-h3 mb-5 text-text">{{ $judulForm }}</h2>

                <form method="post" action="{{ $aksiForm }}" enctype="multipart/form-data" x-data="rincianApbdes({{ Js::from($rincian) }})" class="flex flex-col gap-6">
                    @csrf
                    @if ($adaItem)
                        @method('put')
                    @endif
                    <input type="hidden" name="status" value="{{ $statusAwal }}">

                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-ui.field name="tahun" type="number" label="Tahun" required inputmode="numeric" :value="$item?->tahun" />
                        <x-ui.field name="periode" label="Periode" required :value="$item?->periode ?? ($baru || ! $item ? '1 Januari – 31 Desember' : null)" placeholder="1 Januari – 31 Desember 2026" />
                    </div>

                    {{-- Rincian per jenis; total dihitung otomatis dan tidak diketik. --}}
                    @foreach ($bagian as $jenis => [$judul, $bantuan])
                        <fieldset class="flex flex-col gap-3 rounded-[12px] border border-border p-4" data-jenis="{{ $jenis }}">
                            <legend class="px-1 type-label text-text">{{ $judul }}</legend>
                            <p class="type-body-sm text-muted">{{ $bantuan }}. Angka tanpa huruf, mis. 12.500.000.</p>

                            <ul class="flex flex-col gap-3">
                                <template x-for="(baris, i) in daftar['{{ $jenis }}']" :key="baris.id">
                                    <li class="grid gap-2 sm:grid-cols-[minmax(0,1fr)_170px_44px] sm:items-start">
                                        <label class="min-w-0">
                                            <span class="sr-only" x-text="'Uraian {{ strtolower($judul) }} baris ' + (i + 1)"></span>
                                            <input
                                                type="text" x-model="baris.uraian" maxlength="200" placeholder="Uraian"
                                                x-bind:name="'rincian[{{ $jenis }}][' + i + '][uraian]'"
                                                class="block min-h-11 w-full rounded-control border border-border bg-surface px-3.5 py-2.5 type-body text-text placeholder:text-muted focus:border-primary focus:shadow-[0_0_0_1px_var(--color-primary),0_0_0_4px_rgb(27_94_32/0.18)] focus-visible:outline-none"
                                            >
                                        </label>
                                        <label>
                                            <span class="sr-only" x-text="'Anggaran {{ strtolower($judul) }} baris ' + (i + 1) + ' dalam rupiah'"></span>
                                            <input
                                                type="text" inputmode="numeric" x-model="baris.anggaran" maxlength="20" placeholder="Rp"
                                                x-bind:name="'rincian[{{ $jenis }}][' + i + '][anggaran]'"
                                                class="block min-h-11 w-full rounded-control border border-border bg-surface px-3.5 py-2.5 text-right type-body text-text placeholder:text-muted focus:border-primary focus:shadow-[0_0_0_1px_var(--color-primary),0_0_0_4px_rgb(27_94_32/0.18)] focus-visible:outline-none"
                                            >
                                        </label>
                                        <button type="button" x-on:click="hapus('{{ $jenis }}', i)" x-bind:aria-label="'Hapus baris ' + (i + 1) + ' {{ strtolower($judul) }}'" class="relative inline-flex size-11 items-center justify-center rounded-control border border-border text-danger hover:border-danger hover:bg-danger-soft">
                                            <x-ui.icon name="trash" class="size-4" />
                                        </button>
                                    </li>
                                </template>
                            </ul>

                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <x-ui.button variant="ghost" icon="plus" x-on:click="tambah('{{ $jenis }}')">Tambah baris</x-ui.button>
                                <p class="type-label text-text">Jumlah: <span x-text="total('{{ $jenis }}')" aria-live="polite"></span></p>
                            </div>
                        </fieldset>
                    @endforeach

                    {{-- Dokumen PDF: disimpan di penyimpanan privat dan diunduh lewat rute yang memeriksa status terbit. --}}
                    <div class="flex flex-col gap-3">
                        <p class="type-label text-text">Dokumen PDF</p>
                        @if ($dokumen->isNotEmpty())
                            <ul class="flex flex-col gap-2">
                                @foreach ($dokumen as $dok)
                                    <li class="flex items-center gap-2.5 rounded-control border border-border bg-surface p-3">
                                        <x-ui.icon name="file" class="size-5 shrink-0 text-primary" />
                                        <div class="min-w-0 flex-1">
                                            <p class="type-label-sm text-text">{{ $dok->judul }}</p>
                                            <p class="type-label-sm text-muted">
                                                @if ($dok->path)
                                                    {{ Format::ukuranBerkas($dok->ukuran) }} · diunggah {{ Format::tanggal($dok->created_at) }}
                                                @else
                                                    Berkas belum diunggah
                                                @endif
                                            </p>
                                        </div>
                                        @if ($dok->path)
                                            <x-admin.icon-button icon="eye" :href="route('admin.apbdes.dokumen.unduh', $dok)" :label="'Unduh dokumen: '.$dok->judul" />
                                        @endif
                                        <x-admin.icon-button icon="trash" tone="bahaya" :label="'Hapus dokumen: '.$dok->judul" x-on:click="$dispatch('open-modal', 'hapus-dokumen-{{ $dok->id }}')" />
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        <div class="flex flex-col gap-3 rounded-[10px] border border-dashed border-border p-4">
                            <p class="type-label-sm text-muted">Tambah dokumen baru (opsional)</p>
                            <x-ui.uploader name="dokumen" :max-mb="\App\Support\UnggahBerkas::batasMb('pdf')" accept="application/pdf" accept-label="PDF" :hint="'Hanya PDF · maks. '.round(config('desa.unggahan.pdf_maks_kb') / 1024).' MB · isi berkas diperiksa di server'" />
                            @error('dokumen')
                                <p class="flex items-start gap-1.5 type-body-sm text-danger"><x-ui.icon name="alert" class="mt-0.5 size-4" /><span>{{ $message }}</span></p>
                            @enderror
                            <x-ui.field name="judul_dokumen" label="Judul dokumen" placeholder="Peraturan Desa tentang APBDes Tahun 2026" help="Wajib diisi bila memilih berkas." />
                            <x-ui.field name="keterangan_periode" label="Keterangan periode dokumen" placeholder="Jan–Des 2026" />
                        </div>
                    </div>

                    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <x-ui.button type="submit" name="aksi" value="draf" variant="secondary">Simpan Draf</x-ui.button>
                        <x-ui.button type="submit" name="aksi" value="terbit" icon="check">Simpan &amp; Terbitkan</x-ui.button>
                    </div>
                </form>
            </section>
        </div>

        @foreach ($periode as $i => $p)
            <x-admin.confirm-delete
                :name="'hapus-apbdes-'.$i" title="Hapus periode ini?"
                :description="'Periode APBDes '.$p->tahun.' beserta rincian dan semua dokumen PDF-nya akan dihapus. Tindakan ini tidak dapat dibatalkan.'"
                :action="route('admin.apbdes.destroy', $p)" confirm="Ya, hapus periode"
            />
        @endforeach
        @foreach ($dokumen as $dok)
            <x-admin.confirm-delete
                :name="'hapus-dokumen-'.$dok->id" title="Hapus dokumen ini?"
                :description="'Dokumen “'.$dok->judul.'” akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.'"
                :action="route('admin.apbdes.dokumen.destroy', $dok)" confirm="Ya, hapus dokumen"
            />
        @endforeach
    </div>
@endsection
