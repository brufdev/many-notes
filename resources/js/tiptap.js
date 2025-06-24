import { Editor, Extension, mergeAttributes } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Image from '@tiptap/extension-image';
import Link from '@tiptap/extension-link';
import TaskItem from '@tiptap/extension-task-item';
import TaskList from '@tiptap/extension-task-list';
import Table from '@tiptap/extension-table';
import TableCell from '@tiptap/extension-table-cell';
import TableHeader from '@tiptap/extension-table-header';
import TableRow from '@tiptap/extension-table-row';
import { CodeBlockLowlight } from '@tiptap/extension-code-block-lowlight';
import { common, createLowlight } from 'lowlight';
import DOMPurify from 'dompurify';
import { markedService } from './marked';
import { turndownService } from './turndown';

window.setupEditor = function (options) {
    let content = '';
    let isSavingAllowed = false;

    const prepareTiptapHTML = function(html) {
        return html
            // Prepare plain text code
            .replace(
                /<code\s+([^>]*?)class="language-plaintext"([^>]*?)>/g,
                (match, before, after) => {
                    return `<code${before}${after}>`;
                }
            )
            // Prepare links
            .replace(
                /<a\s+([^>]*?)data-href([^>]*?)>/g,
                (match, before, after) => {
                    return `<a ${before}href${after}>`;
                }
            )
            // Prepare task lists
            .replace(
                /<li([^>]*)>\s*(?:<label[^>]*>)\s*(<input type="checkbox"[^>]*>)(?:<span><\/span><\/label>)(?:<div>)?(?:<p>)?(.*?)(?:<\/p>)?(?:<\/div>)?<\/li>/gs,
                (match, liAttributes, input, content) => {
                    return `<li${liAttributes}">${input}${content}</li>`;
                }
            );
    }

    if (options.content) {
        content = DOMPurify.sanitize(
            markedService.parse(options.content),
        );
    }

    const TableAlignmentCommands = Extension.create({
        name: 'tableAlignmentCommands',

        addCommands() {
            return {
                setTableColumnAlignment: (alignment) => ({ tr, state, dispatch }) => {
                    const { selection } = state;
                    const { $from } = selection;

                    // Find the current cell and column index
                    let columnIndex = -1;
                    let cellDepth = -1;
                    let rowDepth = -1;
                    let tableDepth = -1;

                    // Walk up to find table structure
                    for (let depth = $from.depth; depth > 0; depth--) {
                        const node = $from.node(depth);

                        if (node.type.name === 'tableCell' || node.type.name === 'tableHeader') {
                            cellDepth = depth;
                        } else if (node.type.name === 'tableRow') {
                            rowDepth = depth;
                        } else if (node.type.name === 'table') {
                            tableDepth = depth;
                            break;
                        }
                    }

                    if (tableDepth === -1 || rowDepth === -1 || cellDepth === -1) {
                        return false;
                    }

                    // Get column index
                    columnIndex = $from.index(rowDepth);

                    // Get table node and position
                    const tableNode = $from.node(tableDepth);
                    const tableStart = $from.start(tableDepth);

                    let modified = false;

                    // Iterate through each row to update the column
                    tableNode.descendants((node, pos) => {
                        if (node.type.name === 'tableRow') {
                            // Check if this row has enough columns
                            if (columnIndex < node.childCount) {
                                const targetCell = node.child(columnIndex);

                                // Calculate the actual position of the target cell
                                let cellPos = tableStart + pos + 1;

                                // Add positions of previous cells in this row
                                for (let i = 0; i < columnIndex; i++) {
                                    cellPos += node.child(i).nodeSize;
                                }

                                // Update the cell with new alignment
                                const newAttrs = { ...targetCell.attrs, align: alignment };
                                tr.setNodeMarkup(cellPos, null, newAttrs);
                                modified = true;
                            }

                            // Don't descend into nested structures
                            return false;
                        }
                    })

                    if (modified && dispatch) {
                        dispatch(tr);
                    }

                    return modified;
                }
            }
        }
    });

    return {
        editor: new Editor({
            element: options.element,
            extensions: [
                StarterKit.configure({
                    code: {
                        HTMLAttributes: {
                            class: 'not-prose px-1 py-0.5 text-sm rounded-sm bg-light-base-400 dark:bg-base-700',
                        },
                    },
                    codeBlock: false,
                }),
                CodeBlockLowlight.configure({
                    defaultLanguage: 'plaintext',
                    lowlight: createLowlight(common),
                }).extend({
                    addNodeView() {
                        return ({ editor, node, getPos }) => {
                            const pre = document.createElement('pre');

                            if (navigator.clipboard) {
                                pre.classList.add('relative');
                                const button = document.createElement('button');
                                button.classList.add('absolute', 'top-2', 'right-2', 'w-4', 'h-4', 'focus:outline-none');
                                button.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>`;
                                pre.appendChild(button);
                                button.addEventListener('click', async (e) => {
                                    e.preventDefault();
                                    const pos = getPos();
                                    const node = editor.view.nodeDOM(pos);
                                    const code = node.querySelector('code');
                                    editor.commands.focus();
                                    editor.commands.setTextSelection(pos + 1);
                                    await navigator.clipboard.writeText(code.textContent);
                                });
                            }

                            const code = document.createElement('code');
                            code.classList.add(`language-${node.attrs.language || 'text'}`);
                            pre.appendChild(code);

                            return {
                                dom: pre,
                                contentDOM: code,
                            };
                        }
                    },
                }),
                Image.extend({
                    renderHTML({ HTMLAttributes }) {
                        const { src } = HTMLAttributes;

                        if (src && !src.startsWith('http')) {
                            HTMLAttributes.src = `/files/${options.vaultId}?path=` + src;
                        }

                        return ['img', mergeAttributes(this.options.HTMLAttributes, HTMLAttributes)];
                    },
                }),
                Link.configure({
                    openOnClick: false,
                }).extend({
                    renderHTML({ HTMLAttributes }) {
                        const { href } = HTMLAttributes;

                        if (href && !href.startsWith('http')) {
                            HTMLAttributes.target = '_self';
                            HTMLAttributes['wire:click.prevent'] = `openFilePath('${href}')`;
                            HTMLAttributes['data-href'] = href;
                            delete HTMLAttributes.href;
                        }

                        return ['a', mergeAttributes(this.options.HTMLAttributes, HTMLAttributes), 0];
                    },
                }),
                TaskList,
                TaskItem.configure({
                    nested: true,
                }),
                Table,
                TableRow,
                TableHeader.configure({
                    HTMLAttributes: {
                        class: 'border border-light-base-400 dark:border-base-700 p-2',
                    },
                }).extend({
                    addAttributes() {
                        return {
                            ...this.parent?.(),
                            align: {
                                default: null,
                                parseHTML: element => element.getAttribute('align'),
                                renderHTML: attributes => {
                                    if (!attributes.align) {
                                        return {}
                                    }

                                    return {
                                        align: attributes.align,
                                        style: `text-align: ${attributes.align}`,
                                    }
                                },
                            },
                        }
                    },
                }),
                TableCell.configure({
                    HTMLAttributes: {
                        class: 'border border-light-base-400 dark:border-base-700 p-2',
                    },
                }).extend({
                    addAttributes() {
                        return {
                            ...this.parent?.(),
                            align: {
                                default: null,
                                parseHTML: element => element.getAttribute('align'),
                                renderHTML: attributes => {
                                    if (!attributes.align) {
                                        return {}
                                    }

                                    return {
                                        align: attributes.align,
                                        style: `text-align: ${attributes.align}`,
                                    }
                                },
                            },
                        }
                    },
                }),
                TableAlignmentCommands,
            ],
            content: content,
            editable: options.editable,
            editorProps: {
                attributes: {
                    class: 'h-full focus:outline-none prose !max-w-none dark:prose-invert',
                    placeholder: options.placeholder,
                },
            },
            onCreate: ({ editor }) => {
                const firstNode = editor.state.doc.firstChild;

                if (firstNode && firstNode.type.name === 'paragraph' && firstNode.content.size === 0) {
                    editor.commands.deleteNode('paragraph');
                }

                isSavingAllowed = true;
            },
            onUpdate({ editor }) {
                if (!isSavingAllowed) {
                    return;
                }

                const html = prepareTiptapHTML(editor.getHTML());
                const markdown = turndownService.turndown(html);
                options.onUpdate(markdown);
            },
        }),

        getEditor() {
            return Alpine.raw(this.editor);
        },

        destroyEditor() {
            this.getEditor().destroy();
        },

        isActive(type, opts = {}) {
            return this.getEditor().isActive(type, opts);
        },

        setEditable(editable) {
            this.getEditor().setEditable(editable);
        },

        undo() {
            this.getEditor().chain().focus().undo().run();
        },

        redo() {
            this.getEditor().chain().focus().redo().run();
        },

        toggleBulletList() {
            this.getEditor().chain().focus().toggleBulletList().run();
        },

        toggleOrderedList() {
            this.getEditor().chain().focus().toggleOrderedList().run();
        },

        toggleTaskList() {
            this.getEditor().chain().focus().toggleTaskList().run();
        },

        toggleHeading(opts) {
            this.getEditor().chain().focus().toggleHeading(opts).run();
        },

        setParagraph() {
            this.getEditor().chain().focus().setParagraph().run();
        },

        toggleBlockquote() {
            this.getEditor().chain().focus().toggleBlockquote().run();
        },

        toggleCodeBlock() {
            this.getEditor().chain().focus().toggleCodeBlock().run();
        },

        toggleBold() {
            this.getEditor().chain().focus().toggleBold().run();
        },

        toggleItalic() {
            this.getEditor().chain().focus().toggleItalic().run();
        },

        toggleStrike() {
            this.getEditor().chain().focus().toggleStrike().run();
        },

        toggleCode() {
            this.getEditor().chain().focus().toggleCode().run();
        },

        toggleLink(url) {
            if (url === '') {
                this.getEditor().chain().focus().extendMarkRange('link').unsetLink().run();

                return;
            }

            this.getEditor().chain().focus().extendMarkRange('link').setLink({ href: url }).run();
        },

        setImage(url) {
            this.getEditor().chain().focus().setImage({ src: url, alt: '', title: '' }).run();
        },

        setHorizontalRule() {
            this.getEditor().chain().focus().setHorizontalRule().run();
        },

        insertTable() {
            this.getEditor().chain().focus().insertTable({ rows: 2, cols: 2 }).run();
        },

        deleteTable() {
            this.getEditor().chain().focus().deleteTable().run();
        },

        addColumnBefore() {
            this.getEditor().chain().focus().addColumnBefore().run();
        },

        addColumnAfter() {
            this.getEditor().chain().focus().addColumnAfter().run();
        },

        deleteColumn() {
            this.getEditor().chain().focus().deleteColumn().run();
        },

        addRowBefore() {
            this.getEditor().chain().focus().addRowBefore().run();
        },

        addRowAfter() {
            this.getEditor().chain().focus().addRowAfter().run();
        },

        deleteRow() {
            this.getEditor().chain().focus().deleteRow().run();
        },

        setTableColumnAlignmentLeft() {
            this.getEditor().chain().focus().setTableColumnAlignment('left').run();
        },

        setTableColumnAlignmentCenter() {
            this.getEditor().chain().focus().setTableColumnAlignment('center').run();
        },

        setTableColumnAlignmentRight() {
            this.getEditor().chain().focus().setTableColumnAlignment('right').run();
        },
    }
}
