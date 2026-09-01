import { type NodeViewRenderer } from '@tiptap/core';
import TaskItem from '@tiptap/extension-task-item';
import { type ViewMutationRecord } from '@tiptap/pm/view';

export const CustomTaskItem = TaskItem.extend({
    addNodeView(): NodeViewRenderer {
        const parentNodeView = this.parent?.();

        if (!parentNodeView) {
            throw new Error('TaskItem node view is not available');
        }

        return props => {
            const nodeView = parentNodeView(props);

            (nodeView.dom as HTMLElement).querySelector('label > span')?.remove();

            return {
                ...nodeView,
                ignoreMutation: (mutation: ViewMutationRecord): boolean => {
                    if (mutation.type === 'selection') {
                        return false;
                    }

                    const contentDOM = nodeView.contentDOM;

                    return contentDOM ? !contentDOM.contains(mutation.target) : false;
                },
            };
        };
    },
});
