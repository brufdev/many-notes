import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;
window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: window.location.hostname,
    wsPort: window.location.port !== '' ? window.location.port : 80,
    wsPath: '/ws',
    forceTLS: false,
    enabledTransports: ['ws'],
});
