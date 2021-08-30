<template>
    <sdb-button
        @click="download"
    >
        <slot>
            <span class="icon is-small">
                <i class="fas fa-download"></i>
            </span>
        </slot>
    </sdb-button>
</template>

<script>
    import JsFileDownloader from 'js-file-downloader';
    import SdbButton from '@/Sdb/Button';

    export default {
        components: {
            SdbButton,
        },
        props: {
            url: {type: String},
        },
        emits: [
            'on-success',
            'on-failed',
        ],
        methods: {
            download() {
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
                return false;
            },
        }
    }
</script>
