import { createApp } from 'vue';
const app = createApp({});

// Components
import Carousel from '@/Components/Builder/Content/Carousel';
import Tabs from '@/Components/Builder/Content/Tabs';

app.component('Carousel', Carousel);
app.component('Tabs', Tabs);

// Mount
app.mount('#app');
