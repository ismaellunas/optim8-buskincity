import './bootstrap';

import JsFileDownloader from 'js-file-downloader';
window.JsFileDownloader = JsFileDownloader;

import { LoadingPlugin } from 'vue-loading-overlay';
import { createApp, defineAsyncComponent } from 'vue';
import { loadingOptions } from './../../../resources/js/Libs/defaults';

const appPage = createApp({
    components: {
        BizButton: defineAsyncComponent(() =>
            import('./../../../resources/js/Biz/Button.vue')
        ),
        Carousel: defineAsyncComponent(() =>
            import('./../../../resources/js/Components/Builder/Content/Carousel.vue')
        ),
        Tabs: defineAsyncComponent(() =>
            import('./../../../resources/js/Components/Builder/Content/Tabs.vue')
        ),
        UserList: defineAsyncComponent(() =>
            import('./../../../resources/js/Components/Builder/Content/UserList.vue')
        ),

        // Modules
        EventsCalendar: defineAsyncComponent(() =>
            import('./../../../modules/Booking/Resources/assets/js/PageBuilderComponents/EventsCalendar.vue')
        ),
        FormBuilder: defineAsyncComponent(() =>
            import('./../../../modules/FormBuilder/Resources/assets/js/Form/Builder.vue')
        ),
    },
});

appPage.use(LoadingPlugin, loadingOptions);

appPage.mount("#app-page");
