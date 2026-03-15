import axios from 'axios';
import { defineRule, configure } from 'vee-validate';
import { localize, setLocale } from '@vee-validate/i18n';
import en from '@vee-validate/i18n/dist/locale/en.json';
import * as rules from '@vee-validate/rules';

// Register all vee-validate rules (required, email, min, confirmed, etc.)
Object.entries(rules).forEach(([name, rule]) => {
    if (typeof rule === 'function') {
        defineRule(name, rule);
    }
});

// Configure validation messages
configure({
    generateMessage: localize({ en }),
});
setLocale('en');

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}
