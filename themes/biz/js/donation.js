import './bootstrap';

import FormDonation from './components/FormDonation.vue';
import { LoadingPlugin } from 'vue-loading-overlay';
import { createApp } from 'vue';

const profilePerformerDonation = createApp({
    components: {
        FormDonation,
    },
});
profilePerformerDonation.use(LoadingPlugin, {color: '#3280bf', loader: 'dots', opacity: 0.3, zIndex: 8000});
profilePerformerDonation.mount("#donation");
