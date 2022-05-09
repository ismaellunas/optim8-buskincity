<template>
    <div class="has-text-centered">
        <div ref="qrCode" />
        <a
            v-if="isDownloadable"
            :download="name"
            :href="dataUrl"
            class="button is-primary"
        >
            Download
        </a>
        <div
            v-if="isDownloadable"
            ref="qrCodeDownload"
            class="is-hidden"
        />
    </div>
</template>

<script>
    import QRCode from 'easyqrcodejs';

    export default {
        name: 'PerformerQrCode',

        props: {
            isDownloadable: {
                type: Boolean,
                default: false
            },
            text: {
                type: String,
                default: window.location.href
            },
            logoUrl: {
                type: String,
                default: null,
            },
            name: {
                type: String,
                default: 'qrcode',
            },
        },

        data(props) {
            return {
                dataUrl: null,
                options: {
                    text: props.text,
                    width: 128,
                    height: 128,
                    correctLevel: QRCode.CorrectLevel.H,
                    logo: this.logoUrl,
                    crossOrigin: "anonymous",
                    onRenderingEnd: (qrCodeOptions, dataUrl) => {
                        this.dataUrl = dataUrl;
                    }
                }
            };
        },

        mounted() {
            new QRCode(this.$refs.qrCode, this.options);

            if (this.isDownloadable) {
                setTimeout(() => {
                    this.createDownloadQrCode();
                }, 100);
            }
        },

        methods: {
            createDownloadQrCode() {
                this.options.width = 1000;
                this.options.height = 1000;

                new QRCode(this.$refs.qrCodeDownload, this.options);
            },
        }
    }
</script>