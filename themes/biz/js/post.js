import './bootstrap';

import LoadingOverlay from './components/LoadingOverlay.vue';
import { LoadingPlugin } from 'vue-loading-overlay';
import { components as defaultComponents } from '@/frontend-bootstrap';
import { createApp } from 'vue';

const app = createApp({
    components: {
        ...defaultComponents,
        ...{ LoadingOverlay }
    },
});

app.use(LoadingPlugin, {color: '#3280bf', loader: 'dots', opacity: 0.3, zIndex: 8000});

app.mount("#post-content");
