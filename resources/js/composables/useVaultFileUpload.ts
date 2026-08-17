import VaultNodeImportController from '@/actions/App/Http/Controllers/VaultNodeImportController';
import { useAxiosForm } from '@/composables/useAxiosForm';
import { useToast } from '@/composables/useToast';
import { useVaultRecentFileStore } from '@/stores/vaultRecentFile';
import { useVaultTreeStore } from '@/stores/vaultTree';
import { AppPageProps } from '@/types';
import { VaultNode } from '@/types/vault';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

interface ImportFilesOptions {
    vaultId: number;
    parentId: number | null;
    files: File[];
    onSuccess?: (files: VaultNode[]) => void;
    onFinish?: () => void;
}

export function getUploadableClipboardFiles(event: ClipboardEvent): File[] {
    if (!event.clipboardData) {
        return [];
    }

    const files = Array.from(event.clipboardData.files);
    const types = Array.from(event.clipboardData.types);

    if (files.length === 0 || types.includes('text/html')) {
        return [];
    }

    return files;
}

export function useVaultFileUpload() {
    const vaultRecentFileStore = useVaultRecentFileStore();
    const vaultTreeStore = useVaultTreeStore();
    const page = usePage<AppPageProps>();
    const { createToast } = useToast();

    const uploadMaxFilesizeBytes = computed(
        () => page.props.app?.metadata?.upload_max_filesize_bytes ?? 0
    );
    const uploadAllowedExtensions = computed(
        () => page.props.app?.metadata?.upload_allowed_extensions ?? ''
    );

    const form = useAxiosForm<{
        parent_id: number | null;
        files: File[];
    }>({
        parent_id: null,
        files: [],
    });

    const filesError = computed<string | undefined>(() => {
        const errors = form.errors as unknown as Record<string, string | undefined>;
        const key = Object.keys(errors).find(k => k === 'files' || k.startsWith('files.'));

        return key ? errors[key] : undefined;
    });

    function filterValidFiles(files: File[]): File[] {
        const allowedExtensions = uploadAllowedExtensions.value
            .split(',')
            .map(extension => extension.replaceAll('.', ''));

        return files.filter(file => {
            const extension = file.name.split('.').pop()?.toLowerCase();
            const invalidExtension = !extension || !allowedExtensions.includes(extension);
            const invalidSize = file.size > uploadMaxFilesizeBytes.value;

            return !invalidExtension && !invalidSize;
        });
    }

    function importFiles(options: ImportFilesOptions): void {
        const validFiles = filterValidFiles(options.files);

        if (validFiles.length === 0) {
            createToast('No valid files to import', 'error');
            options.onFinish?.();

            return;
        }

        form.send({
            url: VaultNodeImportController.url({ vault: options.vaultId }),
            method: 'post',
            data: {
                parent_id: options.parentId,
                files: validFiles,
            },
            onError: error => {
                const message = error.response?.statusText ?? 'Something went wrong';
                createToast(message, 'error');
            },
            onSuccess: (response: { files: VaultNode[] }) => {
                if (response.files.length === 0) {
                    createToast('No files were imported', 'error');

                    return;
                }

                const message = response.files.length === 1 ? 'file imported' : 'files imported';
                createToast(`${response.files.length} ${message}`, 'success');

                for (const file of response.files) {
                    vaultRecentFileStore.upsertRecentFile(file);
                    vaultTreeStore.handleNodeSaved(file);
                }

                options.onSuccess?.(response.files);
            },
            onFinish: () => options.onFinish?.(),
        });
    }

    return { form, filesError, filterValidFiles, importFiles };
}
