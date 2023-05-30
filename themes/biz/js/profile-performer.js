import './bootstrap';

import BookingEvents from '@mod/Booking/Resources/assets/js/Frontend/BookingEvents.vue';
import FormDonation from './components/FormDonation.vue';
import Gallery from '@/Components/Builder/Content/Gallery.vue';
import LoadingOverlay from './components/LoadingOverlay.vue';
import { LoadingPlugin } from 'vue-loading-overlay';
import { createApp } from 'vue';

const app = createApp({
    components: {
        BookingEvents,
        FormDonation,
        Gallery,
        LoadingOverlay,
    },
});

app.use(LoadingPlugin, {color: '#3280bf', loader: 'dots', opacity: 0.3, zIndex: 8000});

app.mount("#app");
