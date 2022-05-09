<template>
    <div class="has-text-centered">
        <div ref="qrCode" />
        <div
            ref="qrCodePrint"
            class="is-hidden"
        />
    </div>
</template>

<script>
    import QRCode from 'easyqrcodejs';

    export default {
        name: 'PerformerQrCode',

        props: {
            height: {
                type: Number,
                default: 128
            },
            width: {
                type: Number,
                default: 128
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

        emits: [
            'data-url-download',
            'data-url-print',
        ],

        data(props) {
            return {
                dataUrl: null,
                options: {
                    text: props.text,
                    width: props.width,
                    height: props.height,
                    correctLevel: QRCode.CorrectLevel.H,
                    logo: this.logoUrl,
                    crossOrigin: "anonymous",
                }
            };
        },

        mounted() {
            const options = this.options;

            options['onRenderingEnd'] = (qrCodeOptions, dataUrl) => {
                this.$emit('data-url-download', dataUrl);
            };

            new QRCode(this.$refs.qrCode, options);

            setTimeout(() => {
                this.createPrintQrCode();
            }, 100);
        },

        methods: {
            createPrintQrCode() {
                const options = this.options;

                options.width = 1000;
                options.height = 1000;
                options['onRenderingEnd'] = (qrCodeOptions, dataUrl) => {
                    this.$emit('data-url-print', dataUrl);
                };

                new QRCode(this.$refs.qrCodePrint, options);
            },
        }
    }
</script>