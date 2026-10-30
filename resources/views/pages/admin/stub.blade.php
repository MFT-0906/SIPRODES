@extends('layouts.admin')

@section('title', $judul)
@section('judul', $judul)

{{-- Halaman sementara: isi modul dibuat pada fase 9–10. --}}
@section('content')
    <h1 class="sr-only">{{ $judul }}</h1>
    <x-ui.empty-state
        icon="file" title="Halaman {{ $judul }} sedang disiapkan"
        description="Isi halaman ini akan dibuat pada fase berikutnya."
    />
@endsection
