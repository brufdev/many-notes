import { markedService } from '@/services/marked';
import { CustomCodeBlockLowlight } from '@/services/tiptap/extension-custom-code-block-low-light';
import { CustomImage } from '@/services/tiptap/extension-custom-image';
import { CustomLink } from '@/services/tiptap/extension-custom-link';
import { CustomTableCell } from '@/services/tiptap/extension-custom-table-cell';
import { CustomTableColumnAlign } from '@/services/tiptap/extension-custom-table-column-align';
import { CustomTableHeader } from '@/services/tiptap/extension-custom-table-header';
import { Hashtag } from '@/services/tiptap/extension-hashtag';
import { SmartBracket } from '@/services/tiptap/extension-smart-bracket';
import { turndownService } from '@/services/turndown';
import { Editor } from '@tiptap/core';
import { Table, TableRow } from '@tiptap/extension-table';
import TaskItem from '@tiptap/extension-task-item';
import TaskList from '@tiptap/extension-task-list';
import StarterKit from '@tiptap/starter-kit';
import { common, createLowlight } from 'lowlight';
import { onMounted, onUnmounted, Ref, shallowRef, watch } from 'vue';

interface SetupEditorOptions {
    vaultId: string;
    element: Ref<HTMLElement | null>;
    markdownElement: Ref<HTMLElement | null>;
    autofocus?: boolean;
    content: string;
    isEditMode: Readonly<Ref<boolean>>;
    onUpdate: (markdown: string) => void;
    openFilePath: (path: string) => void;
}

export function useEditor(options: SetupEditorOptions) {
    const editor = shallowRef<Editor | null>(null);
    let isSavingEnabled = false;

    const prepareTiptapHTML = (html: string) => {
        return (
            html
                // Prepare plain text code
                .replace(
                    /<code\s+([^>]*?)class="language-plaintext"([^>]*?)>/g,
                    (match, before, after) => {
                        return `<code${before}${after}>`;
                    }
                )
                // Prepare links
                .replace(/<a\s+([^>]*?)data-href([^>]*?)>/g, (match, before, after) => {
                    return `<a ${before}href${after}>`;
                })
                // Prepare task lists
                .replace(
                    /<li([^>]*)>\s*(?:<label[^>]*>)\s*(<input type="checkbox"[^>]*>)(?:<span><\/span><\/label>)(?:<div>)?(?:<p>)?(.*?)(?:<\/p>)?(?:<\/div>)?<\/li>/gs,
                    (match, liAttributes, input, content) => {
                        return `<li${liAttributes}">${input}${content}</li>`;
                    }
                )
        );
    };

    const encodeText = (text: string) => {
        // Encode paths from Markdown links
        const encoded = text.replace(
            /\[(.*?)\]\((.*?)(\s".*?")?\)/g,
            (match, text, path, title) => {
                if (title === undefined) {
                    title = '';
                }

                try {
                    return `[${text}](${encodeURI(path)}${title})`;
                } catch {
                    return `[${text}](${path}${title})`;
                }
            }
        );

        // Prevent HTML rendering
        return encoded.replace(/</g, '&lt;');
    };

    const content = options.content ? markedService.parse(encodeText(options.content)) : '';

    onMounted(() => {
        editor.value = new Editor({
            element: options.element.value,
            autofocus: options.autofocus,
            extensions: [
                StarterKit.configure({
                    code: {
                        HTMLAttributes: {
                            class: 'not-prose px-1 py-0.5 text-sm rounded-sm bg-light-base-400 dark:bg-base-700',
                        },
                    },
                    codeBlock: false,
                    link: false,
                }),
                SmartBracket,
                Hashtag,
                CustomCodeBlockLowlight.configure({
                    defaultLanguage: 'plaintext',
                    lowlight: createLowlight(common),
                }),
                CustomImage.configure({
                    vaultId: options.vaultId,
                }),
                CustomLink.configure({
                    autolink: false,
                    onOpenFile: href => options.openFilePath(href),
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
            editable: options.isEditMode.value,
            editorProps: {
                attributes: {
                    class: 'h-full !max-w-none flow-root focus:outline-none prose dark:prose-invert',
                },
            },
            onCreate(props) {
                const firstNode = props.editor.state.doc.firstChild;

                if (firstNode?.type.name === 'paragraph' && firstNode.content.size === 0) {
                    props.editor.commands.deleteNode('paragraph');
                }

                isSavingEnabled = true;

                if (options.markdownElement.value) {
                    options.markdownElement.value.textContent = options.content;
                }
            },
            onUpdate() {
                if (!isSavingEnabled) {
                    return;
                }

                const html = prepareTiptapHTML(editor.value?.getHTML() ?? '');
                const markdown = turndownService.turndown(html);

                if (options.markdownElement.value) {
                    options.markdownElement.value.textContent = markdown;
                }

                options.onUpdate(markdown);
            },
        });
    });

    function setContent(content: string, triggerUpdate: boolean = true) {
        isSavingEnabled = false;

        const html = markedService.parse(encodeText(content));
        editor.value?.commands.setContent(html);

        if (triggerUpdate) {
            options.onUpdate(content);
        }

        isSavingEnabled = true;
    }

    watch(options.isEditMode, value => {
        isSavingEnabled = false;
        editor.value?.setEditable(value);
        isSavingEnabled = true;
    });

    onUnmounted(() => {
        editor.value?.destroy();
        editor.value = null;
    });

    return {
        editor,
        setContent,
    };
}
