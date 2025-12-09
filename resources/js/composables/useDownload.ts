import { ref } from 'vue';

export function useDownload() {
    const processing = ref(false);
    const error = ref<string | null>(null);

    const download = async (url: string, filename: string = 'download') => {
        processing.value = true;
        error.value = null;

        try {
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                const contentType = response.headers.get('content-type') ?? '';

                if (contentType.includes('application/json')) {
                    const data = await response.json();
                    error.value = data.message ?? response.statusText;
                } else {
                    error.value = response.statusText;
                }

                return;
            }

            // Extract filename
            const disposition = response.headers.get('Content-Disposition');
            const backendFilename = getFilenameFromDisposition(disposition);

            // Convert response to blob
            const blob = await response.blob();

            // Trigger browser download
            const objectUrl = globalThis.URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = objectUrl;
            link.download = backendFilename || filename;
            document.body.appendChild(link);
            link.click();

            // Cleanup
            link.remove();
            URL.revokeObjectURL(objectUrl);
        } catch (e) {
            error.value = e instanceof Error ? e.message : 'Network error';
        } finally {
            processing.value = false;
        }
    };

    const getFilenameFromDisposition = (disposition: string | null): string | null => {
        if (!disposition) {
            return null;
        }

        const results = /filename="?([^"]+)"?$/.exec(disposition);

        return results?.[1] ?? null;
    };

    return {
        processing,
        error,
        download,
    };
}
