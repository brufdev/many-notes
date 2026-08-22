import { useToast } from '@/composables/useToast';
import type { Errors, FormDataType, HttpRequestHeaders } from '@inertiajs/core';
import { useHttp } from '@inertiajs/vue3';

const GENERIC_MESSAGE = 'Something went wrong';

const STATUS_MESSAGES: Record<number, string> = {
    401: 'Unauthorized',
    403: 'Forbidden',
    404: 'Not Found',
    413: 'Content Too Large',
    419: 'Page Expired',
    429: 'Too Many Requests',
    500: 'Internal Server Error',
    503: 'Service Unavailable',
};

type RequestMethod = 'get' | 'post' | 'put' | 'patch' | 'delete';

export interface RequestOptions<TResponse> {
    headers?: HttpRequestHeaders;
    onSuccess?: (response: TResponse) => void;
    onInvalid?: (errors: Errors) => void;
    onFailure?: (message: string) => void;
    onFinish?: () => void;
}

type RequestSender = <TResponse = unknown>(
    url: string,
    options?: RequestOptions<TResponse>
) => void;

type Request<TForm extends FormDataType<TForm>> = Omit<
    ReturnType<typeof useHttp<TForm>>,
    RequestMethod
> &
    Record<RequestMethod, RequestSender>;

const METHODS = ['get', 'post', 'put', 'patch', 'delete'] as const;

export function useRequest<TForm extends FormDataType<TForm>>(initialData: TForm): Request<TForm> {
    const { createToast } = useToast();
    const http = useHttp<TForm>(initialData);

    const submit = {
        get: http.get,
        post: http.post,
        put: http.put,
        patch: http.patch,
        delete: http.delete,
    };

    function send<TResponse>(
        method: RequestMethod,
        url: string,
        options: RequestOptions<TResponse> = {}
    ): void {
        const fail = (message: string): false => {
            createToast(message, 'error');
            options.onFailure?.(message);

            return false;
        };

        submit[method](url, {
            headers: options.headers,
            onSuccess: response => options.onSuccess?.(response as TResponse),
            onError: errors => options.onInvalid?.(errors),
            onHttpException: response => fail(STATUS_MESSAGES[response.status] ?? GENERIC_MESSAGE),
            onNetworkError: () => fail(GENERIC_MESSAGE),
            onFinish: () => options.onFinish?.(),
        }).catch(() => {});
    }

    const senders = Object.fromEntries(
        METHODS.map(method => [
            method,
            (url: string, options?: RequestOptions<unknown>) => send(method, url, options),
        ])
    ) as Record<RequestMethod, RequestSender>;

    return Object.assign(http, senders) as Request<TForm>;
}
