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

        form.processing = true;
        form.clearErrors();
        form.recentlySuccessful = false;

        axios({
            method,
            url,
            data: data ?? form.data(),
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
