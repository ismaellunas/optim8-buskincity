<template>
    <div class="has-text-centered">
        <div ref="qrCode" />
        <a
            :download="qrCodeLogoName"
            :href="dataUrl"
            class="button is-primary"
        >
            Download
        </a>
        <div
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
            qrCodeUrl: {
                type: String,
                default: window.location.href
            },
            qrCodeLogo: {
                type: String,
                default: null,
            },
            qrCodeLogoName: {
                type: String,
                default: 'qrcode',
            },
        },

        data() {
            return {
                dataUrl: null,
                options: {
                    text: this.qrCodeUrl,
                    width: 150,
                    height: 150,
                    correctLevel: QRCode.CorrectLevel.H,
                    logo: this.qrCodeLogo,
                    crossOrigin: "anonymous",
                    onRenderingEnd: (qrCodeOptions, dataUrl) => {
                        this.dataUrl = dataUrl;
                    }
                }
            };
        },

        mounted() {
            new QRCode(this.$refs.qrCode, this.options);

            setTimeout(() => {
                this.createDownloadQrCode();
            }, 100);
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