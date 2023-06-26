import './bootstrap';

import { createApp, defineAsyncComponent } from 'vue';

const profilePerformerDonation = createApp({
    components: {
        FormDonation: defineAsyncComponent(() =>
            import('./components/FormDonation.vue')
        ),
    },
});

profilePerformerDonation.mount("#donation");
