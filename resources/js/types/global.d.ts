import type EchoClass from 'laravel-echo';
import type { Broadcaster } from 'laravel-echo';
import PusherClass from 'pusher-js';

declare global {
    var Echo: EchoClass<keyof Broadcaster>;
    var Pusher: typeof PusherClass;
}

declare module '@inertiajs/core' {
    interface InertiaConfig {
        flashDataType: {
            status?: string;
            error?: string;
        };
    }
}

export {};
