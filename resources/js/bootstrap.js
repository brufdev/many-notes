import axios from 'axios';
import './echo';
import './tiptap';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
