<template>
    <biz-button
        @click="download($event)"
    >
        <slot>
            <biz-icon
                class="is-small"
                :icon="icon.download"
            />
        </slot>
    </biz-button>
</template>

<script>
    import BizButton from '@/Biz/Button.vue';
    import BizIcon from '@/Biz/Icon.vue';
    import JsFileDownloader from 'js-file-downloader';
    import { download } from '@/Libs/icon-class';

    export default {
        name: 'BizButtonDownload',

        components: {
            BizButton,
            BizIcon,
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

        setup() {
            return {
                icon: { download },
            };
        },

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
