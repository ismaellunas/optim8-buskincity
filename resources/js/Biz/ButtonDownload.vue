<template>
    <biz-button
        @click="download($event)"
    >
        <slot>
            <span class="icon is-small">
                <i class="fas fa-download" />
            </span>
        </slot>
    </biz-button>
</template>

<script>
    import JsFileDownloader from 'js-file-downloader';
    import BizButton from '@/Biz/Button';

    export default {
        name: 'BizButtonDownload',

        components: {
            BizButton,
        },

        props: {
            url: {
                type: String,
                required: true,
            },
        },

        emits: [
            'on-success',
            'on-failed',
        ],

        methods: {
            download(event) {
                event.preventDefault();

                const self = this;

                new JsFileDownloader({
                    url: self.url
                })
                    .then(function () {
                        self.$emit('on-success');
                    })
                    .catch(function (error) {
                        self.$emit('on-failed', error);
                    });
            },
        }
    };
</script>
