import type * as AxiosModule from 'axios';
import type EchoClass from 'laravel-echo';
import type { Broadcaster } from 'laravel-echo';
import PusherClass from 'pusher-js';

declare global {
    var Echo: EchoClass<keyof Broadcaster>;
    var Pusher: typeof PusherClass;
    var axios: typeof AxiosModule.default;
}

export {};
