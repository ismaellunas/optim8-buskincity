import './bootstrap';

import { createApp, defineAsyncComponent } from 'vue';

const qrCode = createApp({
    components: {
        BizQrCode: defineAsyncComponent(() =>
            import('./../../../resources/js/Biz/QrCode.vue')
        ),
    },

    mounted() {
        window.onafterprint = function() {
            window.document.body.onfocus = function() { window.close(); }
        };
    },

    methods: {
        print() {
            window.print();
        }
    },
})

qrCode.mount("#app-qr-code");
