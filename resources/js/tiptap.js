import { Editor, mergeAttributes } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Image from '@tiptap/extension-image';
import Link from '@tiptap/extension-link';
import TaskItem from '@tiptap/extension-task-item';
import TaskList from '@tiptap/extension-task-list';
import Table from '@tiptap/extension-table';
import TableRow from '@tiptap/extension-table-row';
import { common, createLowlight } from 'lowlight';

import { CustomCodeBlockLowlight } from './tiptap/extension-custom-code-block-low-light';
import { CustomTableHeader } from './tiptap/extension-custom-table-header';
import { CustomTableCell } from './tiptap/extension-custom-table-cell';
import { CustomTableColumnAlign } from './tiptap/extension-custom-table-column-align';
import { markedService } from './marked';
import { turndownService } from './turndown';

window.setupEditor = function (options) {
    let content = '';
    let isSavingEnabled = false;
    let isEditingMarkdown = options.isEditingMarkdown;

    const prepareTiptapHTML = (html) => {
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
    };

    const encodeHTML = (text) => {
        // Encode paths from Markdown links
        const encoded = text.replace(
            /\[(.*?)\]\((.*?)(\s".*?")?\)/g,
            (match, text, path, title) => {
                if (title === undefined) title = '';

                return `[${text}](${encodeURI(path)}${title})`;
            },
        );

        // Encode HTML entities
        const map = {
            '<': '&lt;',
            '"': '&quot;',
            "'": '&#039;'
        };

        return encoded.replace(/[<"']/g, (char) => map[char]);
    };

    if (options.content) {
        content = markedService.parse(encodeHTML(options.content));
    }

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
                CustomCodeBlockLowlight.configure({
                    defaultLanguage: 'plaintext',
                    lowlight: createLowlight(common),
                }),
                Image.extend({
                    renderHTML({ HTMLAttributes }) {
                        const { src } = HTMLAttributes;

                        if (src && !src.startsWith('http://') && !src.startsWith('https://')) {
                            HTMLAttributes.src = `/files/${options.vaultId}?path=${src}`;
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
                CustomTableHeader.configure({
                    HTMLAttributes: {
                        class: 'border border-light-base-400 dark:border-base-700 p-2',
                    },
                }),
                CustomTableCell.configure({
                    HTMLAttributes: {
                        class: 'border border-light-base-400 dark:border-base-700 p-2',
                    },
                }),
                CustomTableColumnAlign,
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

                isSavingEnabled = true;
            },
            onUpdate({ editor }) {
                if (!isSavingEnabled) {
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
            isSavingEnabled = false;
            this.getEditor().setEditable(editable);
            isSavingEnabled = true;
        },

        setContent(content) {
            const html = markedService.parse(encodeHTML(content));
            this.getEditor().commands.setContent(html, {
                emitUpdate: true,
            });
        },

        toggleMarkdown() {
            if (isEditingMarkdown) {
                options.markdownElement.classList.add('hidden');
                options.element.classList.remove('hidden');
            } else {
                this.updateMarkdown();
                options.element.classList.add('hidden');
                options.markdownElement.classList.remove('hidden');
            }

            isEditingMarkdown = !isEditingMarkdown;
        },

        updateMarkdown() {
            const html = prepareTiptapHTML(this.getEditor().getHTML());
            const markdown = turndownService.turndown(html);
            options.markdownElement.value = markdown;
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
    };
};
