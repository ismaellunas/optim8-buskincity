import './bootstrap';

import LoadingOverlay from './components/LoadingOverlay.vue';
import { createApp } from "vue";

const app = createApp({
        components: {
            LoadingOverlay,
        },
    });

app.mount("#app");
