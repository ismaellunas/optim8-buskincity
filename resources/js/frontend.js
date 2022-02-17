import { createApp } from 'vue';
import { components as defaultComponents } from './frontend-bootstrap';

const app = createApp({
    components: defaultComponents
});

app.mount('#app');
