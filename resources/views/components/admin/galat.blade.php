{{-- Ringkasan galat validasi di atas formulir admin. --}}
@if ($errors->any())
    <x-ui.alert tone="bahaya" title="Perubahan gagal disimpan. Periksa isian yang ditandai lalu coba lagi." {{ $attributes }}>
        <ul class="mt-1 list-disc pl-5">
            @foreach ($errors->all() as $pesan)
                <li>{{ $pesan }}</li>
            @endforeach
        </ul>
    </x-ui.alert>
@endif
