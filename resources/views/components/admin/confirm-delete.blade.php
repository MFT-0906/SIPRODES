@props(['name', 'title', 'description', 'action', 'confirm' => 'Ya, hapus']) {{-- dialog konfirmasi hapus: buka dengan $dispatch('open-modal', name) --}}

<x-ui.modal :name="$name" :title="$title" :description="$description">
    <x-slot:footer>
        <form method="post" action="{{ $action }}" class="contents">
            @csrf
            @method('delete')
            <x-ui.button variant="secondary" x-on:click="$dispatch('close-modal', {{ Js::from($name) }})">Batal</x-ui.button>
            <x-ui.button type="submit" variant="danger">{{ $confirm }}</x-ui.button>
        </form>
    </x-slot:footer>
</x-ui.modal>
