import { createApp } from 'vue';
const app = createApp({});

// Components
import Carousel from '@/Components/Builder/Content/Carousel';
app.component('Carousel', Carousel);

import Tabs from '@/Components/Builder/Content/Tabs';
app.component('Tabs', Tabs);

// Mount
app.mount('#app');
