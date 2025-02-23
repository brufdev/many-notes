@props(['node']) 

<li class="items-center justify-between XXXpy-0.5 my-0.5" draggable="true"
    x-data="{ accordionOpen: false }"
    {{ $attributes }}
    @dragstart.stop="event.dataTransfer.setData('text/plain', '{{ $node->id }}')"
    @dragenter.prevent=""
    @dragover.prevent=""
    @drop="moveNode(event)"
>
    {{ $slot }}
</li>
