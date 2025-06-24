import { CodeBlockLowlight } from '@tiptap/extension-code-block-lowlight';

export const CustomCodeBlockLowlight = CodeBlockLowlight.extend({
    addNodeView() {
        return ({ editor, node, getPos }) => {
            const pre = document.createElement('pre');

            if (navigator.clipboard) {
                const button = document.createElement('button');
                button.classList.add('absolute', 'top-2', 'right-2', 'w-4', 'h-4', 'focus:outline-none');
                button.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>';
                button.addEventListener('click', async (e) => {
                    e.preventDefault();

                    const pos = getPos();
                    const node = editor.view.nodeDOM(pos);
                    const code = node.querySelector('code');
                    editor.commands.focus();
                    editor.commands.setTextSelection(pos + 1);
                    await navigator.clipboard.writeText(code.textContent);
                });

                pre.classList.add('relative');
                pre.appendChild(button);
            }

            const code = document.createElement('code');
            code.classList.add(`language-${node.attrs.language || 'text'}`);
            pre.appendChild(code);

            return {
                dom: pre,
                contentDOM: code,
            };
        };
    },
});
