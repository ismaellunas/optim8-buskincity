<template>
    <biz-media-gallery
        :media="value"
        :column-class="columnClass"
        :is-delete-enabled="false"
        :is-download-enabled="true"
        :is-edit-enabled="false"
    />
</template>

<script>
    import BizMediaGallery from '@/Biz/Media/Gallery.vue';

    export default {
        name: 'MediaGallery',

        components: {
            BizMediaGallery
        },

        props: {
            value: { type: Object, default: () => {} },
        },

        computed: {
            columnClass() {
                return [
                    'column',
                    'is-6'
                ];
            },
        },
    }
</script>