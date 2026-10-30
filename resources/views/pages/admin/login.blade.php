@extends('layouts.auth')

@section('title', 'Masuk ke Dashboard')

@section('content')
    <div class="flex w-full flex-col gap-5 rounded-[20px] border border-border bg-surface p-6 shadow-[0_16px_40px_rgb(15_51_33/0.08)] sm:p-10">
        <x-public.logo tone="terang" class="self-start" />

        <div class="flex flex-col gap-1.5">
            <h1 class="type-h2 text-text">Masuk ke Dashboard</h1>
            <p class="type-body-sm text-muted">Khusus perangkat desa yang memiliki akun.</p>
        </div>

        {{-- Pesan gagal bersifat umum: tidak menyebut apakah email atau kata sandi yang salah. --}}
        @if (session('gagal_masuk'))
            <x-ui.alert tone="bahaya">{{ session('gagal_masuk') }}</x-ui.alert>
        @endif

        {{-- TODO(backend): autentikasi, penguncian sementara setelah beberapa kali gagal. --}}
        <form method="post" action="{{ route('admin.login.store') }}" class="flex flex-col gap-5" novalidate>
            @csrf
            <x-ui.field
                name="login" label="Email atau username" required
                placeholder="nama@margaagung.desa.id" autocomplete="username" autofocus
            />
            <x-ui.field
                name="password" type="password" label="Kata sandi" required
                autocomplete="current-password"
                help="Akun terkunci sementara setelah beberapa kali gagal masuk."
            />

            <div class="flex items-center justify-between gap-3">
                <x-ui.checkbox name="ingat" label="Ingat saya" />
                <a href="{{ route('admin.password.request') }}" class="inline-flex min-h-11 items-center rounded-control type-label text-primary hover:underline">Lupa kata sandi?</a>
            </div>

            <x-ui.button type="submit" icon="arrow-right" class="w-full">Masuk</x-ui.button>
        </form>

        <p class="flex items-center gap-1.5 type-label-sm text-muted">
            <x-ui.icon name="lock" class="size-3.5 shrink-0" />
            Koneksi aman · lupa kata sandi dibantu Super Admin
        </p>
    </div>

    <a href="{{ route('home') }}" class="inline-flex min-h-11 items-center rounded-control type-label text-primary hover:underline">← Kembali ke situs publik</a>
@endsection
