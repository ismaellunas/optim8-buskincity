import JsFileDownloader from 'js-file-downloader';
window.JsFileDownloader = JsFileDownloader;

import BizButton from '@/Biz/Button.vue';
import BizQrCode from '@/Biz/QrCode.vue';
import Carousel from '@/Components/Builder/Content/Carousel.vue';
import Tabs from '@/Components/Builder/Content/Tabs.vue';
import UserList from '@/Components/Builder/Content/UserList.vue';

// Modules
import FormBuilder from '@mod/FormBuilder/Resources/assets/js/Form/Builder.vue';
import EventsCalendar from '@mod/Booking/Resources/assets/js/PageBuilderComponents/EventsCalendar.vue';
import SpaceEvents from '@mod/Space/Resources/assets/js/Frontend/SpaceEvents.vue';

export const components = {
    BizButton,
    BizQrCode,
    Carousel,
    Tabs,
    UserList,

    // Modules
    EventsCalendar,
    FormBuilder,
    SpaceEvents,
};
