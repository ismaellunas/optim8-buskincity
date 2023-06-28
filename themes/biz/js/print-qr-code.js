import './bootstrap';

import { createApp, defineAsyncComponent } from 'vue';

const qrCode = createApp({
    components: {
        BizQrCode: defineAsyncComponent(() =>
            import('./../../../resources/js/Biz/QrCode.vue')
        ),
    },
})

qrCode.mount("#app-qr-code");
