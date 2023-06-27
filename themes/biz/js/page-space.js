import './bootstrap';

import { createApp, defineAsyncComponent } from 'vue';

const appPageSpace = createApp({
    components: {
        SpaceEvents: defineAsyncComponent(() =>
            import('./../../../modules/Space/Resources/assets/js/Frontend/SpaceEvents.vue')
        ),
    },
});

appPageSpace.mount("#app-page-space");
