<template>
    <div class="has-text-centered">
        <div ref="qrCode" />
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
            options: { type: Object, default: () => {} },
        },

        emits: [
            'on-rendered',
        ],

        data(props) {
            return {
                dataUrl: null,
                mergedOptions: {
                    ...{
                        text: props.text,
                        width: props.width,
                        height: props.height,
                        logo: this.logoUrl,
                        crossOrigin: "anonymous",
                        quietZone: props.height * 3 / 100,
                    },
                    ...props.options,
                }
            };
        },

        mounted() {
            const options = this.mergedOptions;

            options['onRenderingEnd'] = (qrCodeOptions, dataUrl) => {
                this.$emit('on-rendered', dataUrl);
            };

            new QRCode(this.$refs.qrCode, options);
        },
    }
</script>
