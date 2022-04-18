/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require("./bootstrap");

/**
 * We will create a fresh Vue application instance.
 */
import ExampleComponent from './components/ExampleComponent';
import FormDonation from './components/FormDonation';
import LoadingOverlay from './components/LoadingOverlay';
import PerformerQrCode from './components/PerformerQrCode';
import { components as defaultComponents } from '@/frontend-bootstrap';
import { createApp } from "vue";

const components = {
    ExampleComponent,
    FormDonation,
    LoadingOverlay,
    PerformerQrCode,
};

const app = createApp({
    components: Object.assign(defaultComponents, components),
});

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => app.component(key.split('/').pop().split('.')[0], files(key).default))

/**
 * Next, attach Vue application instance to the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */
app.mount("#app");
