import JsFileDownloader from 'js-file-downloader';
window.JsFileDownloader = JsFileDownloader;

import BizButton from '@/Biz/Button';
import BizQrCode from '@/Biz/QrCode';
import Carousel from '@/Components/Builder/Content/Carousel';
import Gallery from '@/Components/Builder/Content/Gallery';
import Tabs from '@/Components/Builder/Content/Tabs';
import UserList from '@/Components/Builder/Content/UserList';

// Modules
import FormBuilder from '@mod/FormBuilder/Resources/assets/js/Form/Builder';
import EventCalendar from '@mod/Booking/Resources/assets/js/PageBuilderComponents/EventCalendar';

export const components = {
    BizButton,
    BizQrCode,
    Carousel,
    Gallery,
    Tabs,
    UserList,

    // Modules
    EventCalendar,
    FormBuilder,
};
