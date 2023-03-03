<template>
    <div :style="dimensionStyle">
        <biz-toolbar-content
            @delete-content="deleteContent"
            @duplicate-content="duplicateContent"
        />
        <biz-image
            ratio="is-16by9"
            :src="media?.default_video"
        />
    </div>
</template>

<script>
    import MixinContentHasDimension from '@/Mixins/ContentHasDimension';
    import MixinDeletableContent from '@/Mixins/DeletableContent';
    import MixinDuplicableContent from '@/Mixins/DuplicableContent';
    import BizImage from '@/Biz/Image.vue';
    import BizToolbarContent from '@/Blocks/Contents/ToolbarContent.vue';

    export default {
        name: 'ContentVideo',

        components: {
            BizImage,
            BizToolbarContent,
        },

        mixins: [
            MixinContentHasDimension,
            MixinDeletableContent,
            MixinDuplicableContent
        ],

        inject: ['media'],
    };
</script>
