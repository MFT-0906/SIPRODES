@extends('layouts.admin')

@section('title', 'Kelola Agenda')
@section('judul', 'Agenda')
@section('breadcrumb', 'Dashboard / Publikasi / Agenda')

@php
    use App\Support\Format;
    use Carbon\Carbon;

    $ubah = ! is_null($item);
    $statusAwal = old('status', $item['status'] ?? 'draf');
    $opsiWaktu = ['mendatang' => 'Mendatang', 'lewat' => 'Sudah lewat', 'semua' => 'Semua'];
    $opsiStatus = ['semua' => 'Semua', 'terbit' => 'Terbit', 'draf' => 'Draf'];
    $grid = 'lg:grid lg:grid-cols-[minmax(0,1fr)_200px_200px_110px_136px] lg:items-center lg:gap-4';
@endphp

@section('content')
    <div class="flex flex-col gap-6">
        <x-admin.page-header title="Kelola Agenda" description="Agenda berstatus “Terbit” dan bertanggal mendatang tampil di situs publik.">
            <x-ui.button :href="route('admin.agenda.index', ['waktu' => request('waktu')]).'#form-agenda'" icon="plus">Tambah Agenda</x-ui.button>
        </x-admin.page-header>

        <section aria-label="Daftar agenda" class="rounded-[14px] border border-border bg-surface">
            <div class="flex flex-col gap-3 p-4 lg:flex-row lg:items-center lg:p-5">
                <form method="get" action="{{ route('admin.agenda.index') }}" role="search" class="lg:w-[300px]">
                    @foreach (['waktu', 'status'] as $kunci)
                        @if (request($kunci))
                            <input type="hidden" name="{{ $kunci }}" value="{{ request($kunci) }}">
                        @endif
                    @endforeach
                    <x-ui.search name="q" label="Cari agenda" placeholder="Cari agenda…" />
                </form>
                <x-ui.filter-dropdown name="waktu" label="Waktu" :options="$opsiWaktu" />
                <x-ui.filter-dropdown name="status" label="Status" :options="$opsiStatus" />
            </div>

            @if ($agenda->count())
                <table class="block w-full">
                    <caption class="sr-only">Daftar agenda beserta tanggal, lokasi, status, dan aksi</caption>
                    <thead class="block">
                        <tr class="hidden border-t border-border bg-background px-5 py-3 type-label-sm text-muted lg:grid lg:grid-cols-[minmax(0,1fr)_200px_200px_110px_136px] lg:gap-4">
                            <th scope="col" class="text-left font-[inherit]">JUDUL AGENDA</th>
                            <th scope="col" class="text-left font-[inherit]">TANGGAL &amp; WAKTU</th>
                            <th scope="col" class="text-left font-[inherit]">LOKASI</th>
                            <th scope="col" class="text-left font-[inherit]">STATUS</th>
                            <th scope="col" class="text-right font-[inherit]">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="block">
                        @foreach ($agenda as $i => $a)
                            @php
                                $lewat = Carbon::parse($a['tanggal'])->lt($hariIni);
                                $tampilPublik = $a['status'] === 'terbit' && ! $lewat;
                            @endphp
                            <tr class="flex flex-col gap-3 border-t border-border px-4 py-4 lg:px-5 {{ $grid }}">
                                <th scope="row" class="min-w-0 text-left type-label text-text">{{ $a['title'] }}</th>
                                <td class="type-body-sm text-muted">
                                    <span class="type-label-sm lg:sr-only">Tanggal &amp; waktu: </span>{{ Format::tanggal($a['tanggal']) }} · {{ $a['waktu'] ? str_replace(':', '.', $a['waktu']) : 'waktu menyusul' }}
                                </td>
                                <td class="type-body-sm text-muted">
                                    <span class="type-label-sm lg:sr-only">Lokasi: </span>{{ $a['location'] ?? '—' }}
                                </td>
                                <td><x-admin.status-badge :status="$lewat ? 'lewat' : $a['status']" /></td>
                                <td class="flex items-center gap-1.5 lg:justify-end">
                                    @if ($tampilPublik)
                                        <x-admin.icon-button
                                            icon="eye" :href="route('berita.index').'#agenda-judul'" :new-tab="true"
                                            :label="'Lihat agenda di situs publik: '.$a['title']"
                                        />
                                    @endif
                                    <x-admin.icon-button
                                        icon="edit" :href="route('admin.agenda.index', ['ubah' => $a['id'], 'waktu' => request('waktu')]).'#form-agenda'"
                                        :label="'Ubah agenda: '.$a['title']"
                                    />
                                    <x-admin.icon-button
                                        icon="trash" tone="bahaya" :label="'Hapus agenda: '.$a['title']"
                                        x-on:click="$dispatch('open-modal', 'hapus-agenda-{{ $i }}')"
                                    />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="flex flex-col gap-3 border-t border-border p-4 sm:flex-row sm:items-center sm:justify-between lg:px-5">
                    <p class="type-body-sm text-muted">Menampilkan {{ $agenda->firstItem() }}–{{ $agenda->lastItem() }} dari {{ $agenda->total() }} agenda</p>
                    <x-ui.pagination :paginator="$agenda" />
                </div>
            @else
                <div class="border-t border-border p-4 lg:p-5">
                    @if ($disaring || $ada)
                        <x-ui.empty-state icon="calendar" title="Tidak ada agenda yang cocok" description="Coba kata kunci lain atau ubah penyaring waktu dan status.">
                            <x-slot:action>
                                <x-ui.button :href="route('admin.agenda.index')" variant="secondary">Hapus filter</x-ui.button>
                            </x-slot:action>
                        </x-ui.empty-state>
                    @else
                        <x-ui.empty-state icon="calendar" title="Belum ada agenda" description="Tambahkan agenda pertama melalui formulir di bawah." />
                    @endif
                </div>
            @endif
        </section>

        {{-- Form agenda --}}
        <section id="form-agenda" aria-labelledby="form-agenda-judul" class="scroll-mt-24 rounded-[14px] border border-border bg-surface p-5 lg:p-6">
            <h2 id="form-agenda-judul" class="type-h3 mb-5 text-text">{{ $ubah ? 'Ubah Agenda' : 'Tambah Agenda' }}</h2>

            @if ($errors->any())
                <x-ui.alert tone="bahaya" title="Periksa kembali isian Anda" class="mb-5">
                    <ul class="mt-1 list-disc pl-5">
                        @foreach ($errors->all() as $pesan)
                            <li>{{ $pesan }}</li>
                        @endforeach
                    </ul>
                </x-ui.alert>
            @endif

            <form
                method="post" x-data="{ status: {{ Js::from($statusAwal) }} }" class="flex flex-col gap-5"
                action="{{ $ubah ? route('admin.agenda.update', $item['id']) : route('admin.agenda.store') }}"
            >
                @csrf
                @if ($ubah)
                    @method('put')
                @endif

                <div class="grid gap-4 md:grid-cols-3">
                    <x-ui.field name="title" label="Judul agenda" required :value="$item['title'] ?? null" placeholder="Nama kegiatan" />
                    <x-ui.field name="tanggal" type="date" label="Tanggal" required :value="$item['tanggal'] ?? null" />
                    <x-ui.field name="waktu" type="time" label="Waktu" :value="$item['waktu'] ?? null" help="Kosongkan jika belum pasti." />
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <x-ui.field name="lokasi" label="Lokasi" :value="$item['location'] ?? null" placeholder="Tempat kegiatan" help="Hanya isi jika lokasi sudah terverifikasi." />
                    <x-ui.select
                        name="status" label="Status" required :placeholder="null"
                        :options="['draf' => 'Draf', 'terbit' => 'Terbit']" :selected="$statusAwal" x-model="status"
                    />
                </div>
                <x-ui.field name="deskripsi" label="Deskripsi" multiline :rows="4" :value="$item['deskripsi'] ?? null" placeholder="Keterangan singkat kegiatan…" />

                {{-- Tombol yang ditekan menentukan status akhir. --}}
                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <x-ui.button :href="route('admin.agenda.index', ['waktu' => request('waktu')])" variant="ghost">Batal</x-ui.button>
                    <x-ui.button type="submit" name="aksi" value="draf" variant="secondary" x-on:click="status = 'draf'">Simpan Draf</x-ui.button>
                    <x-ui.button type="submit" name="aksi" value="terbit" icon="check" x-on:click="status = 'terbit'">Simpan &amp; Terbitkan</x-ui.button>
                </div>
            </form>
        </section>

        @foreach ($agenda as $i => $a)
            <x-ui.modal
                :name="'hapus-agenda-'.$i" title="Hapus agenda ini?"
                :description="'Agenda “'.$a['title'].'” akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.'"
            >
                <x-slot:footer>
                    <form method="post" action="{{ route('admin.agenda.destroy', $a['id']) }}" class="contents">
                        @csrf
                        @method('delete')
                        <x-ui.button variant="secondary" x-on:click="$dispatch('close-modal', 'hapus-agenda-{{ $i }}')">Batal</x-ui.button>
                        <x-ui.button type="submit" variant="danger">Ya, hapus agenda</x-ui.button>
                    </form>
                </x-slot:footer>
            </x-ui.modal>
        @endforeach
    </div>
@endsection
