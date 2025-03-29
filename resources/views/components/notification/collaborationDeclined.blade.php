@props(['notification'])

<x-menu.item title="{{ __('Click to dismiss') }}" @click="$wire.delete('{{ $notification['id'] }}')">
    {{ $notification['message'] }}
</x-menu.item>
