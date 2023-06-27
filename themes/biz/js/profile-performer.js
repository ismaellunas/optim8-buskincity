import './bootstrap';

import { createApp, defineAsyncComponent } from 'vue';

const profilePerformer = createApp({
    components: {
        FormDonation: defineAsyncComponent(() =>
            import('./components/FormDonation.vue')
        ),
        Gallery: defineAsyncComponent(() =>
            import('./../../../resources/js/Components/Builder/Content/Gallery.vue')
        ),
        BookingEvents: defineAsyncComponent(() =>
            import('./../../../modules/Booking/Resources/assets/js/Frontend/BookingEvents.vue')
        ),
    },
})

profilePerformer.mount("#app-vue");
