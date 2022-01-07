import { createApp } from 'vue';
import JsFileDownloader from 'js-file-downloader';

const app = createApp({});
window.JsFileDownloader = JsFileDownloader;

// Components
import Carousel from '@/Components/Builder/Content/Carousel';
import Tabs from '@/Components/Builder/Content/Tabs';

app.component('Carousel', Carousel);
app.component('Tabs', Tabs);

// Mount
app.mount('#app');
