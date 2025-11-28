import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

globalThis.Pusher = Pusher;

globalThis.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: globalThis.location.hostname,
    wsPort: globalThis.location.port ? Number.parseInt(globalThis.location.port) : 80,
    wsPath: '/ws',
    forceTLS: false,
    enabledTransports: ['ws'],
});
