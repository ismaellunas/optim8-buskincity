<template>
    <div :style="dimensionStyle">
        <biz-toolbar-content
            @delete-content="deleteContent"
            @duplicate-content="duplicateContent"
        />
        <biz-image
            ratio="is-16by9"
            src="https://dummyimage.com/640x360/ccc/fff.png&text=Video"
        />
    </div>
</template>

<script>
    import MixinContentHasDimension from '@/Mixins/ContentHasDimension';
    import MixinDeletableContent from '@/Mixins/DeletableContent';
    import MixinDuplicableContent from '@/Mixins/DuplicableContent';
    import BizToolbarContent from '@/Blocks/Contents/ToolbarContent';
    import BizImage from '@/Biz/Image';

    export default {
        name: 'Video',

        components: {
            BizToolbarContent,
            BizImage,
        },

        mixins: [
            MixinContentHasDimension,
            MixinDeletableContent,
            MixinDuplicableContent
        ],
    }
</script>
