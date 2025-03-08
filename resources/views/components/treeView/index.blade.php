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
            moveNodeId: null,

            moving() {
                return this.moveNodeId !== null;
            },

            moveNode(nodeId) {
                this.moveNodeId = nodeId;
            },

            dropNode(nodeId) {
                if (!this.dropNodeAllowed(nodeId)) {
                    return;
                }

                const args = [this.moveNodeId];

                if (nodeId > 0) {
                    args.push(nodeId);
                }

                $wire.moveNode(...args);
                this.moveNodeId = null;
            },

            dropNodeAllowed(nodeId) {
                return typeof this.moveNodeId === 'number' && this.moveNodeId > 0 &&
                    typeof nodeId === 'number' && nodeId >= 0;
            },

            showDropZone(nodes) {
                return !nodes.includes(this.moveNodeId.toString());
            }
        }));
    </script>
@endscript
