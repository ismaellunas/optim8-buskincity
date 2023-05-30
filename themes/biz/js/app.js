/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

import './bootstrap';

/**
 * We will create a fresh Vue application instance.
 */
import FormDonation from './components/FormDonation.vue';
import LoadingOverlay from './components/LoadingOverlay.vue';
import { components as defaultComponents } from '@/frontend-bootstrap';
import { createApp } from 'vue';
import { LoadingPlugin } from 'vue-loading-overlay';

const components = {
    FormDonation,
    LoadingOverlay,
};

const app = createApp({
    components: {
        ...defaultComponents,
        ...components
    },
});
app.use(LoadingPlugin, {color: '#3280bf', loader: 'dots', opacity: 0.3, zIndex: 8000});

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => app.component(key.split('/').pop().split('.')[0], files(key).default))

/**
 * Next, attach Vue application instance to the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */
app.mount("#app");
