import './bootstrap';

import BookingEvents from '@mod/Booking/Resources/assets/js/Frontend/BookingEvents.vue';
import FormDonation from './components/FormDonation.vue';
import Gallery from '@/Components/Builder/Content/Gallery.vue';
import { LoadingPlugin } from 'vue-loading-overlay';
import { createApp } from 'vue';

const profilePerformer = createApp({
    components: {
        BookingEvents,
        FormDonation,
        Gallery,
    },
})
profilePerformer.use(LoadingPlugin, {color: '#3280bf', loader: 'dots', opacity: 0.3, zIndex: 8000})
profilePerformer.mount("#app");
