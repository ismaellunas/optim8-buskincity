<template>
    <div class="column is-half">
        <h2
            class="title is-4"
            :class="{'mt-5': order != 0}"
        >
            {{ title }}
        </h2>
        <div class="box is-shadowless">
            <div class="columns">
                <div class="column is-narrow">
                    <biz-qr-code
                        :height="128"
                        :width="128"
                        :text="data.text"
                        :logo-url="data.logoUrl"
                        :name="data.name"
                        @data-url-download="setDownloadUrl"
                        @data-url-print="setPrintUrl"
                    />
                </div>

                <div class="column">
                    <p>Print your QR code and place it on your pitch. It will allow your audience to find you on BuskinCity, send donations, book you for private gigs, or follow your work.</p>

                    <div class="buttons are-small mt-5">
                        <a
                            :href="qrCodeUrl.download"
                            class="button is-primary"
                            :download="data.name"
                        >
                            <span class="has-text-weight-bold">Download</span>
                        </a>
                        <a
                            :href="qrCodeUrl.print"
                            class="button"
                            :download="data.name"
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
    import BizQrCode from '@/Biz/QrCode';

    export default {
        name: 'QrCode',

        components: {
            BizQrCode,
        },

        props: {
            data: {type: Object, required: true},
            title: {type: String, default: ""},
            order: {type: Number, required: true},
        },

        data() {
            return {
                qrCodeUrl: {
                    download: null,
                    print: null,
                },
            };
        },

        methods: {
            setDownloadUrl(url) {
                this.qrCodeUrl.download = url;
            },

            setPrintUrl(url) {
                this.qrCodeUrl.print = url;
            },
        },
    };
</script>
