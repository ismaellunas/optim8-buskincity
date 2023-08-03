import './bootstrap';

import { LoadingPlugin } from 'vue-loading-overlay';
import { createApp, defineAsyncComponent } from 'vue';
import { loadingOptions } from './../../../resources/js/Libs/defaults';

const appPostContent = createApp({
    components: {
        FormBuilderSlotable: defineAsyncComponent(() =>
            import('./../../../modules/FormBuilder/Resources/assets/js/Form/BuilderSlotable.vue')
        ),

        FormFileDragDrop: defineAsyncComponent(() =>
            import('./../../../resources/js/Form/FileDragDrop.vue')
        ),
        FormPhone: defineAsyncComponent(() =>
            import('./../../../modules/FormBuilder/Resources/assets/js/Form/Phone.vue')
        ),
    },
});

appPostContent.use(LoadingPlugin, loadingOptions);

appPostContent.mount("#post-content");
