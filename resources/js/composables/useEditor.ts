import { markedService } from '@/services/marked';
import { CustomCodeBlockLowlight } from '@/services/tiptap/extension-custom-code-block-low-light';
import { CustomImage } from '@/services/tiptap/extension-custom-image';
import { CustomLink } from '@/services/tiptap/extension-custom-link';
import { CustomTableCell } from '@/services/tiptap/extension-custom-table-cell';
import { CustomTableColumnAlign } from '@/services/tiptap/extension-custom-table-column-align';
import { CustomTableHeader } from '@/services/tiptap/extension-custom-table-header';
import { CustomTaskItem } from '@/services/tiptap/extension-custom-task-item';
import { Hashtag } from '@/services/tiptap/extension-hashtag';
import { SmartBracket } from '@/services/tiptap/extension-smart-bracket';
import { VaultFileDrop } from '@/services/tiptap/extension-vault-file-drop';
import {
    VaultFileUpload,
    VaultFileUploadRequest,
} from '@/services/tiptap/extension-vault-file-upload';
import { turndownService } from '@/services/turndown';
import { Editor } from '@tiptap/core';
import { Table, TableRow } from '@tiptap/extension-table';
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
    uploadFiles: (request: VaultFileUploadRequest) => void;
}

export function useEditor(options: SetupEditorOptions) {
    const editor = shallowRef<Editor | null>(null);
    let isSyncing: boolean = true;

    const prepareTiptapHTML = (html: string) => {
        const doc = new DOMParser().parseFromString(html, 'text/html');

        // Prepare plain text code
        doc.querySelectorAll('code.language-plaintext').forEach(element => {
            element.classList.remove('language-plaintext');

            if (element.classList.length === 0) {
                element.removeAttribute('class');
            }
        });

        // Prepare links
        doc.querySelectorAll<HTMLAnchorElement>('a[data-href]').forEach(element => {
            element.setAttribute('href', element.dataset.href ?? '');
            delete element.dataset.href;
        });

        return doc.body.innerHTML;
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
                CustomTaskItem.configure({
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
                VaultFileDrop,
                VaultFileUpload.configure({
                    uploadFiles: options.uploadFiles,
                    placeholderClass:
                        'bg-light-base-300 dark:bg-base-800 text-light-base-700 dark:text-base-200 rounded-sm px-1 text-sm',
                }),
            ],
            content: content,
            editable: options.isEditMode.value,
            editorProps: {
                attributes: {
                    class: 'h-full !max-w-none flow-root focus:outline-none prose dark:prose-invert',
                },
            },
            onCreate() {
                isSyncing = false;

                setMarkdownContent(options.content);
            },
            onUpdate() {
                if (isSyncing) {
                    return;
                }

                isSyncing = true;

                const html = prepareTiptapHTML(editor.value?.getHTML() ?? '');
                const markdown = turndownService.turndown(html);
                setMarkdownContent(markdown);

                isSyncing = false;

                options.onUpdate(markdown);
            },
        });
    });

    function setTiptapContent(html: string) {
        editor.value?.commands.setContent(html);
    }

    function setMarkdownContent(markdown: string) {
        if (options.markdownElement.value) {
            options.markdownElement.value.textContent = markdown;
        }
    }

    async function setContent(markdown: string) {
        isSyncing = true;

        const html = await markedService.parse(encodeText(markdown));
        setTiptapContent(html);
        setMarkdownContent(markdown);

        isSyncing = false;
    }

    async function onMarkdownChanged(markdown: string) {
        if (isSyncing) {
            return;
        }

        isSyncing = true;

        const html = await markedService.parse(encodeText(markdown));
        setTiptapContent(html);

        isSyncing = false;

        options.onUpdate(markdown);
    }

    watch(options.isEditMode, value => {
        isSyncing = true;

        editor.value?.setEditable(value);

        isSyncing = false;
    });

    onUnmounted(() => {
        editor.value?.destroy();
        editor.value = null;
    });

    return {
        editor,
        setContent,
        onMarkdownChanged,
    };
}
