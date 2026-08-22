import type EchoClass from 'laravel-echo';
import type { Broadcaster } from 'laravel-echo';
import PusherClass from 'pusher-js';
import type { AppPageProps } from './index';

declare global {
    var Echo: EchoClass<keyof Broadcaster>;
    var Pusher: typeof PusherClass;
}

declare module '@inertiajs/core' {
    interface InertiaConfig {
        sharedPageProps: AppPageProps;
        flashDataType: {
            status?: string;
            error?: string;
        };
    }
}

export {};
