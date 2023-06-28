import './bootstrap';

import { LoadingPlugin } from 'vue-loading-overlay';
import { createApp, defineAsyncComponent } from 'vue';
import { loadingOptions } from './../../../resources/js/Libs/defaults';

const appPostContent = createApp({
    components: {
        FormBuilder: defineAsyncComponent(() =>
            import('./../../../modules/FormBuilder/Resources/assets/js/Form/Builder.vue')
        ),
    },
});

appPostContent.use(LoadingPlugin, loadingOptions);

appPostContent.mount("#post-content");
