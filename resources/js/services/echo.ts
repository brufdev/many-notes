import { configureEcho } from '@laravel/echo-vue';

const isSecure = globalThis.location.protocol === 'https:';
const defaultPort = isSecure ? 443 : 80;
const port = globalThis.location.port
    ? Number.parseInt(globalThis.location.port, 10)
    : defaultPort;

configureEcho({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: globalThis.location.hostname,
    wsPort: port,
    wssPort: port,
    wsPath: '/ws',
    forceTLS: isSecure,
    enabledTransports: ['ws', 'wss'],
});
