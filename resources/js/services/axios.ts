import axios from 'axios';

globalThis.axios = axios;
globalThis.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
