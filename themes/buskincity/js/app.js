import './../../biz/js/bootstrap';

import { LoadingPlugin } from 'vue-loading-overlay';
import { createApp, defineAsyncComponent } from 'vue';
import { loadingOptions } from './../../../resources/js/Libs/defaults';

const app = createApp({
    components: {
        LoadingOverlay: defineAsyncComponent(() =>
            import('./../../biz/js/components/LoadingOverlay.vue')
        ),
    },
});

app.use(LoadingPlugin, loadingOptions);

app.mount("#app");
