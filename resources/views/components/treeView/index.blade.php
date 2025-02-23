<div class="flex flex-col flex-grow w-full" x-data="treeView"
    @treeview-enter-node.window="enterNode($event.detail.event)"
    @treeview-leave-node.window="leaveNode($event.detail.event)"
    @treeview-move-node.window="moveNode($event.detail.event)"
>
    {{ $slot }}
</div>

@script
    <script>
        Alpine.data('treeView', () => ({
            moveNode(event) {
                const sourceId = event.dataTransfer.getData('text/plain');
                const targetId = event.target.closest('a').dataset.id;

                if (!+sourceId || (targetId.length && !+targetId) || sourceId == targetId) {
                    return;
                }

                const args = [sourceId];

                if (targetId.length) {
                    args.push(targetId);
                }

                $wire.moveNode(...args);
            }
        }));
    </script>
@endscript
