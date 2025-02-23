@props(['node'])

<x-treeView.item :$node>
    @if (!$node->is_file)
        <x-treeView.itemFolder />

        @if (!empty($node->children) && $node->children->count())
            @include('components.vault.treeViewNode', ['nodes' => $node->children, 'root' => false])
        @endif
    @else
        <x-treeView.itemFile />
    @endif
</x-treeView.item>
