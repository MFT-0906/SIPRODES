@extends('layouts.admin')

@section('title', 'Akun & Hak Akses')
@section('judul', 'Akun & Hak Akses')
@section('breadcrumb', 'Dashboard / Administrasi / Akun & Hak Akses')

@php
    use App\Enums\Modul;
    use App\Support\Format;

    $cols = 'minmax(0,1.3fr) 150px 120px 130px 110px';
    $kolomIzin = 'minmax(0,1fr) 160px 160px';
    $bukaBaru = $errors->any() && old('_mode') === 'baru';
    $peranAwal = old('peran', $ubah?->peran->value ?? 'staf');
    $modulAwal = $ubah ? $ubah->modulAkses->pluck('modul')->map(fn ($m) => $m->value)->all() : [];
    $teksPeran = ['super_admin' => 'Super Admin', 'staf' => 'Staf'];
@endphp

@section('content')
    <div class="flex flex-col gap-6">
        <x-admin.page-header title="Akun & Hak Akses" description="Hanya Super Admin yang dapat membuka halaman ini. Izin diperiksa di server.">
            <x-ui.button icon="plus" x-on:click="$dispatch('open-modal', 'akun-baru')">Tambah Akun</x-ui.button>
        </x-admin.page-header>

        <section aria-label="Daftar akun" class="rounded-[14px] border border-border bg-surface">
            <x-admin.table :cols="$cols" caption="Daftar akun admin beserta peran, status, dan login terakhir" :heads="[['PENGGUNA', 'left'], ['PERAN', 'left'], ['STATUS', 'left'], ['LOGIN TERAKHIR', 'left'], ['AKSI', 'right']]">
                @foreach ($daftar as $a)
                    <x-admin.tr :cols="$cols" class="first:border-t-0">
                        <th scope="row" class="flex min-w-0 items-center gap-3 text-left font-[inherit]">
                            <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-primary-soft text-primary" aria-hidden="true"><x-ui.icon name="user" class="size-[18px]" /></span>
                            <span class="min-w-0">
                                <span class="block type-label text-text">{{ $a->name }}</span>
                                <span class="block type-body-sm text-muted">{{ $a->email }}@if ($a->username) · {{ $a->username }}@endif</span>
                            </span>
                        </th>
                        <td><x-ui.badge :tone="$a->peran->value === 'super_admin' ? 'aksen' : 'sukses'">{{ $teksPeran[$a->peran->value] }}</x-ui.badge></td>
                        <td><x-ui.badge :tone="$a->aktif ? 'sukses' : 'netral'">{{ $a->aktif ? 'Aktif' : 'Nonaktif' }}</x-ui.badge></td>
                        <td class="type-body-sm text-muted"><span class="type-label-sm lg:sr-only">Login terakhir: </span>{{ $a->terakhir_masuk_at ? Format::tanggal($a->terakhir_masuk_at) : 'Belum pernah' }}</td>
                        <td class="flex items-center gap-1.5 lg:justify-end">
                            <x-admin.icon-button icon="edit" :href="route('admin.akun.index', ['ubah' => $a->id])" :label="'Ubah akun: '.$a->name" />
                            <form method="post" action="{{ route('admin.akun.reset', $a) }}">
                                @csrf
                                <x-admin.icon-button icon="lock" :submit="true" :label="'Kirim tautan atur ulang kata sandi: '.$a->name" class="text-warning!" />
                            </form>
                        </td>
                    </x-admin.tr>
                @endforeach
            </x-admin.table>
        </section>

        {{-- Matriks izin: nilai bawaan, perlu konfirmasi desa. Staf memegang modul yang ditugaskan per akun. --}}
        <section aria-labelledby="izin-judul" class="rounded-[14px] border border-border bg-surface">
            <div class="flex flex-col gap-1 p-5">
                <h2 id="izin-judul" class="type-h3 text-text">Matriks Izin (nilai bawaan — perlu konfirmasi desa)</h2>
                <p class="type-body-sm text-muted">Menyembunyikan menu di UI tidak menggantikan pemeriksaan izin di server.</p>
            </div>
            <x-admin.table :cols="$kolomIzin" caption="Matriks izin modul per peran" :heads="[['MODUL', 'left'], ['SUPER ADMIN', 'right'], ['STAF', 'right']]">
                @foreach (Modul::cases() as $modul)
                    @php($izinPeran = ['super_admin' => 'ya', 'staf' => $modul->dapatDitugaskanKeStaf() ? 'penugasan' : 'tidak'])
                    <x-admin.tr :cols="$kolomIzin" class="!py-3">
                        <th scope="row" class="text-left type-label text-text">{{ $modul->label() }}</th>
                        @foreach ($izinPeran as $peran => $nilai)
                            <td class="flex items-center gap-1.5 lg:justify-end">
                                <span class="type-label-sm text-muted lg:sr-only">{{ $teksPeran[$peran] }}: </span>
                                @if ($nilai === 'ya')
                                    <x-ui.icon name="check" class="size-4 text-success" /><span class="type-body-sm text-success">Ya</span>
                                @elseif ($nilai === 'tidak')
                                    <x-ui.icon name="x" class="size-4 text-muted" /><span class="type-body-sm text-muted">Tidak</span>
                                @else
                                    <span class="type-body-sm text-warning">Sesuai penugasan</span>
                                @endif
                            </td>
                        @endforeach
                    </x-admin.tr>
                @endforeach
            </x-admin.table>
        </section>

        {{-- Modal Tambah Akun: kata sandi tidak diisi admin; pengguna menerima tautan. --}}
        <x-ui.modal name="akun-baru" title="Tambah Akun Admin" size="lg" :open="$bukaBaru">
            <form id="form-akun-baru" method="post" action="{{ route('admin.akun.store') }}" class="flex flex-col gap-4">
                @csrf
                <input type="hidden" name="_mode" value="baru">
                <x-ui.field name="nama" id="baru-nama" label="Nama lengkap" required :error="$bukaBaru ? $errors->first('nama') : null" placeholder="Nama perangkat desa" />
                <x-ui.field name="email" id="baru-email" type="email" label="Email" required :error="$bukaBaru ? $errors->first('email') : null" placeholder="nama@…" help="Tautan membuat kata sandi dikirim ke email ini." />
                <x-ui.field name="username" id="baru-username" label="Username" :error="$bukaBaru ? $errors->first('username') : null" help="Opsional. Huruf, angka, titik, garis bawah, atau tanda hubung." />
                <fieldset>
                    <legend class="type-label text-text">Peran</legend>
                    <div class="mt-1 flex flex-wrap gap-x-6">
                        <x-ui.radio name="peran" id="baru-peran-staf" value="staf" label="Staf" :checked="true" />
                        <x-ui.radio name="peran" id="baru-peran-super" value="super_admin" label="Super Admin" />
                    </div>
                    @if ($bukaBaru && $errors->has('peran'))
                        <p class="type-body-sm text-danger" role="alert">{{ $errors->first('peran') }}</p>
                    @endif
                </fieldset>
                <fieldset>
                    <legend class="type-label text-text">Modul untuk Staf</legend>
                    <p class="type-body-sm text-muted">Hanya berlaku untuk peran Staf. Super Admin otomatis memegang semua modul.</p>
                    <div class="mt-1 grid gap-x-6 sm:grid-cols-2">
                        @foreach ($modulStaf as $m)
                            <x-ui.checkbox name="modul[]" id="baru-modul-{{ $m->value }}" :value="$m->value" :label="$m->label()" />
                        @endforeach
                    </div>
                </fieldset>
                <x-ui.alert tone="info">Kata sandi tidak diisi admin. Pengguna baru menerima tautan untuk membuat kata sandinya sendiri.</x-ui.alert>
            </form>
            <x-slot:footer>
                <x-ui.button variant="secondary" x-on:click="$dispatch('close-modal', 'akun-baru')">Batal</x-ui.button>
                <x-ui.button type="submit" form="form-akun-baru">Buat Akun</x-ui.button>
            </x-slot:footer>
        </x-ui.modal>

        @if ($ubah)
            <x-ui.modal name="akun-ubah" title="Ubah Akun Admin" size="lg" :open="true">
                <form id="form-akun-ubah" method="post" action="{{ route('admin.akun.update', $ubah) }}" class="flex flex-col gap-4">
                    @csrf
                    @method('put')
                    <input type="hidden" name="_mode" value="ubah">
                    <x-ui.field name="nama" id="ubah-nama" label="Nama lengkap" required :value="$ubah->name" />
                    <x-ui.field name="email" id="ubah-email" type="email" label="Email" required :value="$ubah->email" />
                    <x-ui.field name="username" id="ubah-username" label="Username" :value="$ubah->username" help="Opsional." />
                    <fieldset>
                        <legend class="type-label text-text">Peran</legend>
                        <div class="mt-1 flex flex-wrap gap-x-6">
                            <x-ui.radio name="peran" id="ubah-peran-staf" value="staf" label="Staf" :checked="$peranAwal === 'staf'" />
                            <x-ui.radio name="peran" id="ubah-peran-super" value="super_admin" label="Super Admin" :checked="$peranAwal === 'super_admin'" />
                        </div>
                        @error('peran')
                            <p class="type-body-sm text-danger" role="alert">{{ $message }}</p>
                        @enderror
                    </fieldset>
                    <fieldset>
                        <legend class="type-label text-text">Modul untuk Staf</legend>
                        <p class="type-body-sm text-muted">Hanya berlaku untuk peran Staf.</p>
                        <div class="mt-1 grid gap-x-6 sm:grid-cols-2">
                            @foreach ($modulStaf as $m)
                                <x-ui.checkbox name="modul[]" id="ubah-modul-{{ $m->value }}" :value="$m->value" :label="$m->label()" :checked="in_array($m->value, $modulAwal, true)" />
                            @endforeach
                        </div>
                    </fieldset>
                    <x-ui.toggle name="aktif" id="ubah-aktif" label="Status akun" :checked="$ubah->aktif" />
                    @error('aktif')
                        <p class="type-body-sm text-danger" role="alert">{{ $message }}</p>
                    @enderror
                </form>
                <x-slot:footer>
                    <x-ui.button :href="route('admin.akun.index')" variant="secondary">Batal</x-ui.button>
                    <x-ui.button type="submit" form="form-akun-ubah">Simpan</x-ui.button>
                </x-slot:footer>
            </x-ui.modal>
        @endif
    </div>
@endsection
