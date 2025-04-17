@props(['nodes', 'root'])

<x-treeView.items :root="$root">
    @foreach ($nodes as $node)
        <x-vault.treeViewRow :$node />
    @endforeach
</x-treeView.items>
