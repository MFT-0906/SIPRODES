@extends('layouts.admin')

@section('title', 'Kelola APBDes')
@section('judul', 'APBDes')
@section('breadcrumb', 'Dashboard / Konten Desa / APBDes')

@php
    $cols = 'minmax(0,1.2fr) 100px 120px 100px';
    $rupiah = fn ($n) => is_null($n) ? null : number_format($n, 0, ',', '.');
    $judulForm = $baru || is_null($item) ? 'Tambah Periode APBDes' : 'Ringkasan APBDes '.$item['tahun'];
    $aksiForm = $baru || is_null($item) ? route('admin.apbdes.store') : route('admin.apbdes.update', $item['id']);
    $statusAwal = old('status', $item['status'] ?? 'draf');
@endphp

@section('content')
    <div class="flex flex-col gap-6">
        <x-admin.page-header title="Kelola APBDes" description="Kelola ringkasan dan dokumen PDF per tahun/periode anggaran.">
            <x-ui.button :href="route('admin.apbdes', ['baru' => 1]).'#form-apbdes'" icon="plus">Tambah Periode</x-ui.button>
        </x-admin.page-header>

        <x-admin.galat />

        <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_544px]">
            <section aria-label="Daftar periode APBDes" class="rounded-[14px] border border-border bg-surface">
                @if ($periode->count())
                    <x-admin.table :cols="$cols" caption="Daftar periode APBDes beserta jumlah dokumen dan status" :heads="[['PERIODE', 'left'], ['DOKUMEN', 'left'], ['STATUS', 'left'], ['AKSI', 'right']]">
                        @foreach ($periode as $i => $p)
                            <x-admin.tr :cols="$cols" :aktif="$item && $item['id'] === $p['id']" class="first:border-t-0">
                                <th scope="row" class="text-left font-[inherit]">
                                    <a href="{{ route('admin.apbdes', ['periode' => $p['id']]) }}#form-apbdes" class="inline-flex min-h-11 items-center type-label text-text hover:underline">APBDes {{ $p['tahun'] }}</a>
                                </th>
                                <td class="type-body-sm text-muted"><span class="type-label-sm lg:sr-only">Dokumen: </span>{{ count($p['dokumen']) }} PDF</td>
                                <td><x-admin.status-badge :status="$p['status']" /></td>
                                <td class="flex items-center gap-1.5 lg:justify-end">
                                    <x-admin.icon-button icon="edit" :href="route('admin.apbdes', ['periode' => $p['id']]).'#form-apbdes'" :label="'Ubah APBDes '.$p['tahun']" />
                                    <x-admin.icon-button icon="trash" tone="bahaya" :label="'Hapus APBDes '.$p['tahun']" x-on:click="$dispatch('open-modal', 'hapus-apbdes-{{ $i }}')" />
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

                <form method="post" action="{{ $aksiForm }}" enctype="multipart/form-data" x-data="{ status: {{ Js::from($statusAwal) }} }" class="flex flex-col gap-5">
                    @csrf
                    @if (! $baru && $item)
                        @method('put')
                    @endif
                    <input type="hidden" name="status" x-bind:value="status">

                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-ui.field name="tahun" type="number" label="Tahun" required inputmode="numeric" :value="$item['tahun'] ?? null" />
                        <x-ui.field name="periode" label="Periode" required :value="$item['periode'] ?? null" placeholder="Jan – Des" />
                    </div>
                    <x-ui.field name="pendapatan" label="Total pendapatan (Rp)" required inputmode="decimal" :value="$rupiah($item['pendapatan'] ?? null)" placeholder="Sesuai dokumen resmi" help="Gunakan angka dari dokumen APBDes yang telah ditetapkan." />
                    <x-ui.field name="belanja" label="Total belanja (Rp)" required inputmode="decimal" :value="$rupiah($item['belanja'] ?? null)" placeholder="Sesuai dokumen resmi" />

                    <div class="flex flex-col gap-3">
                        <p class="type-label text-text">Dokumen PDF</p>
                        {{-- Pemeriksaan di klien hanya untuk kenyamanan; jenis dan isi berkas wajib diperiksa di server. --}}
                        <x-ui.uploader name="dokumen" accept="application/pdf" accept-label="PDF" hint="Hanya PDF · isi berkas diperiksa di server" />
                        @error('dokumen')
                            <p class="flex items-start gap-1.5 type-body-sm text-danger"><x-ui.icon name="alert" class="mt-0.5 size-4" /><span>{{ $message }}</span></p>
                        @enderror
                        @if (! empty($item['dokumen']))
                            <ul class="flex flex-col gap-2">
                                @foreach ($item['dokumen'] as $dok)
                                    <li class="flex items-center gap-2.5 rounded-control border border-border bg-surface p-3">
                                        <x-ui.icon name="file" class="size-5 shrink-0 text-primary" />
                                        <div class="min-w-0 flex-1">
                                            <p class="type-label-sm text-text">{{ $dok['judul'] }}</p>
                                            <p class="type-label-sm text-muted">{{ $dok['url'] ? 'Berkas tersedia' : 'Berkas belum diunggah' }}</p>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>

                    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <x-ui.button type="submit" name="aksi" value="draf" variant="secondary" x-on:click="status = 'draf'">Simpan Draf</x-ui.button>
                        <x-ui.button type="submit" name="aksi" value="terbit" icon="check" x-on:click="status = 'terbit'">Simpan &amp; Terbitkan</x-ui.button>
                    </div>
                </form>
            </section>
        </div>

        @foreach ($periode as $i => $p)
            <x-admin.confirm-delete
                :name="'hapus-apbdes-'.$i" title="Hapus periode ini?"
                :description="'Periode APBDes '.$p['tahun'].' beserta ringkasan dan daftar dokumennya akan dihapus. Tindakan ini tidak dapat dibatalkan.'"
                :action="route('admin.apbdes.destroy', $p['id'])" confirm="Ya, hapus periode"
            />
        @endforeach
    </div>
@endsection
