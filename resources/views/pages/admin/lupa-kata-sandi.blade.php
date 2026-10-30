@extends('layouts.auth')

@section('title', 'Lupa kata sandi')

@section('content')
    <div class="flex w-full flex-col gap-[18px] rounded-[20px] border border-border bg-surface p-6 sm:p-10">
        <x-public.logo tone="terang" class="self-start" />

        <h1 class="type-h2 text-text">Lupa kata sandi?</h1>
        <p class="type-body-sm text-muted">Masukkan email akun Anda. Jika terdaftar, kami kirim tautan atur ulang kata sandi. Bila tidak menerima email, hubungi Super Admin desa.</p>

        {{-- TODO(backend): kirim tautan hanya bila email terdaftar; pesan konfirmasi tetap sama. --}}
        <form method="post" action="{{ route('admin.password.email') }}" class="flex flex-col gap-[18px]" novalidate>
            @csrf
            <x-ui.field name="email" type="email" label="Email" required placeholder="nama@…" autocomplete="email" />
            <x-ui.button type="submit" icon="mail" class="w-full">Kirim tautan atur ulang</x-ui.button>
        </form>

        {{-- Pesan yang sama untuk email terdaftar maupun tidak terdaftar. --}}
        @if (session('tautan_terkirim'))
            <x-ui.alert tone="sukses">Jika email terdaftar, tautan telah dikirim. Pesan ini sama untuk email yang tidak terdaftar.</x-ui.alert>
        @endif

        <a href="{{ route('admin.login') }}" class="inline-flex min-h-11 items-center self-start rounded-control type-label text-primary hover:underline">← Kembali ke halaman masuk</a>
    </div>
@endsection
