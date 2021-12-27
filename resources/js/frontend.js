import { createApp } from 'vue';
const app = createApp({});

// Components
import Carousel from '@/Components/Builder/Content/Carousel';
app.component('Carousel', Carousel);

// Mount
app.mount('#app');
