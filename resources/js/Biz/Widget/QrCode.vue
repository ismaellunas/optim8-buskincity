<template>
    <div class="column is-6-desktop is-6-tablet is-12-mobile">
        <h2 class="title is-4">
            {{ title }}
        </h2>
        <div class="box is-shadowless">
            <div class="columns is-multiline is-mobile">
                <div class="column is-4-desktop is-12-tablet is-12-mobile">
                    <biz-qr-code
                        :height="data.dimension.default.height"
                        :width="data.dimension.default.width"
                        :text="data.text"
                        :name="data.name"
                        :logo-url="data.logoThumbnailUrl"
                        @on-rendered="setDownloadUrl"
                    />
                </div>

                <div class="column is-8-desktop is-12-tablet is-12-mobile">
                    <p>
                        {{ data.description }}
                    </p>

                    <div class="buttons are-small mt-5">
                        <biz-button
                            type="button"
                            class="is-primary"
                            @click="download"
                        >
                            <span class="has-text-weight-bold">Download</span>
                        </biz-button>

                        <a
                            :href="route('frontend.print.qrcode', { user: data.uniqueKey })"
                            class="button"
                            target="_blank"
                        >
                            <span class="has-text-weight-bold">Print</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import MixinHasLoader from '@/Mixins/HasLoader';
    import BizButton from '@/Biz/Button.vue';
    import BizQrCode from '@/Biz/QrCode.vue';
    import QRCode from 'easyqrcodejs';

    export default {
        name: 'QrCode',

        components: {
            BizButton,
            BizQrCode,
        },

        mixins: [
            MixinHasLoader,
        ],

        props: {
            data: {type: Object, required: true},
            title: {type: String, default: ""},
            order: {type: Number, required: true},
        },

        data() {
            return {
                qrCodeUrl: {
                    download: null,
                },
            };
        },

        methods: {
            setDownloadUrl(url) {
                this.qrCodeUrl.download = url;
            },

            download() {
                let qrElement = document.createElement('div');

                const options = {
                    ...{
                        logo: this.data.logoUrl,
                        crossOrigin: "anonymous",
                        onRenderingStart: () => {
                            this.onStartLoadingOverlay();
                        },
                        onRenderingEnd: (qrCodeOptions, dataUrl) => {
                            this.onEndLoadingOverlay();
                            this.downloadBase64File(dataUrl, this.data.name + '.png');
                            qrElement.remove();
                        }
                    },
                    ...this.data.qrOptions,
                }

                new QRCode(qrElement, options);
            },

            downloadBase64File(href, fileName) {
                const downloadLink = document.createElement("a");
                downloadLink.href = href;
                downloadLink.download = fileName;
                downloadLink.click();
                downloadLink.remove();
            }
        },
    };
</script>
