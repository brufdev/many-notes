import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import TaskItem from '@tiptap/extension-task-item';
import TaskList from '@tiptap/extension-task-list';
import Table from '@tiptap/extension-table';
import TableRow from '@tiptap/extension-table-row';
import { common, createLowlight } from 'lowlight';
import { SmartBracket } from './tiptap/extension-smart-bracket';
import { CustomCodeBlockLowlight } from './tiptap/extension-custom-code-block-low-light';
import { CustomImage } from './tiptap/extension-custom-image';
import { CustomLink } from './tiptap/extension-custom-link';
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

    const encodeText = (text) => {
        // Encode paths from Markdown links
        const encoded = text.replace(
            /\[(.*?)\]\((.*?)(\s".*?")?\)/g,
            (match, text, path, title) => {
                if (title === undefined) {
                    title = '';
                }

                try {
                    return `[${text}](${encodeURI(path)}${title})`;
                } catch (error) {
                    return `[${text}](${path}${title})`;
                }
            },
        );

        // Prevent HTML rendering
        return encoded.replace(/</g, '&lt;');
    };

    if (options.content) {
        content = markedService.parse(encodeText(options.content));
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
                SmartBracket,
                CustomCodeBlockLowlight.configure({
                    defaultLanguage: 'plaintext',
                    lowlight: createLowlight(common),
                }),
                CustomImage.configure({
                    vaultId: options.vaultId,
                }),
                CustomLink.configure({
                    autolink: false,
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
                options.markdownElement.textContent = options.content;
            },
            onUpdate({ editor }) {
                if (!isSavingEnabled) {
                    return;
                }

                const html = prepareTiptapHTML(editor.getHTML());
                const markdown = turndownService.turndown(html);
                options.markdownElement.textContent = markdown;
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
            const html = markedService.parse(encodeText(content));
            this.getEditor().commands.setContent(html);
            options.onUpdate(content);
        },

        toggleMarkdown() {
            if (isEditingMarkdown) {
                options.markdownElement.classList.add('hidden');
                options.element.classList.remove('hidden');
            } else {
                options.element.classList.add('hidden');
                options.markdownElement.classList.remove('hidden');
            }

            isEditingMarkdown = !isEditingMarkdown;
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
