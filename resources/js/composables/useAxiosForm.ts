import { type FormDataErrors, type FormDataType } from '@inertiajs/core';
import { InertiaForm, useForm } from '@inertiajs/vue3';
import { AxiosError, AxiosRequestConfig, Method } from 'axios';

type AxiosSend<TForm> = <TResponse = unknown>(options: {
    url: string;
    method?: Method;
    data?: Partial<TForm>;
    axiosConfig?: AxiosRequestConfig;
    onSuccess?: (response: TResponse) => void;
    onError?: (error: AxiosError) => void;
    onFinish?: () => void;
}) => void;

function hasFiles(value: unknown): boolean {
    if (value instanceof File || value instanceof Blob) {
        return true;
    }

    if (Array.isArray(value)) {
        return value.some(hasFiles);
    }

    if (typeof value === 'object' && value !== null) {
        return Object.values(value).some(hasFiles);
    }

    return false;
}

function toFormData(
    data: Record<string, unknown>,
    formData = new FormData(),
    parentKey?: string
): FormData {
    for (const key in data) {
        const value = data[key];
        const fullKey = parentKey ? `${parentKey}[${key}]` : key;

        if (value instanceof File || value instanceof Blob) {
            formData.append(fullKey, value);
        } else if (Array.isArray(value)) {
            value.forEach((v, index) => toFormData({ [index]: v }, formData, fullKey));
        } else if (value !== null && typeof value === 'object') {
            toFormData(value as Record<string, unknown>, formData, fullKey);
        } else if (value === null) {
            formData.append(fullKey, '');
        } else if (value !== undefined) {
            formData.append(fullKey, String(value));
        }
    }

    return formData;
}

function normalizeErrors(errors: Record<string, string[]>): Record<string, string> {
    const result: Record<string, string> = {};

    for (const key in errors) {
        result[key] = errors[key][0];
    }

    return result;
}

export function useAxiosForm<TForm extends FormDataType<TForm>>(
    initialData: TForm
): InertiaForm<TForm> & { send: AxiosSend<TForm> } {
    const form = useForm<TForm>(initialData);

    const send: AxiosSend<TForm> = options => {
        const { url, method, data, axiosConfig, onSuccess, onError, onFinish } = options;

        const payload = data ?? form.data();
        const isMultipart = hasFiles(payload);
        const requestData = isMultipart ? toFormData(payload as Record<string, unknown>) : payload;

        form.processing = true;
        form.clearErrors();
        form.recentlySuccessful = false;

        axios({
            method,
            url,
            data: requestData,
            ...axiosConfig,
        })
            .then(response => {
                form.recentlySuccessful = true;
                onSuccess?.(response.data);
            })
            .catch((error: AxiosError<{ errors?: Record<string, string[]> }>) => {
                if (error.response?.status === 422) {
                    const rawErrors = error.response.data?.errors ?? {};
                    const normalizedErrors = normalizeErrors(rawErrors);
                    form.setError(normalizedErrors as unknown as FormDataErrors<TForm>);

                    return;
                }

                onError?.(error);
            })
            .finally(() => {
                form.processing = false;
                onFinish?.();
            });
    };

    return Object.assign(form, { send });
}
