<template>
    <div :style="dimensionStyle">
        <biz-toolbar-content
            @delete-content="deleteContent"
            @duplicate-content="duplicateContent"
        />
        <div
            class="content"
            :class="contentClass"
        >
            <biz-tinymce
                v-model="entity.content.html"
            />
        </div>
    </div>
</template>

<script>
    import MixinContentHasDimension from '@/Mixins/ContentHasDimension';
    import MixinDeletableContent from '@/Mixins/DeletableContent';
    import MixinDuplicableContent from '@/Mixins/DuplicableContent';
    import BizTinymce from '@/Biz/EditorTinymce.vue';
    import BizToolbarContent from '@/Blocks/Contents/ToolbarContent.vue';
    import { concat } from 'lodash';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'ContentText',
        components: {
            BizTinymce,
            BizToolbarContent,
        },
        mixins: [
            MixinContentHasDimension,
            MixinDeletableContent,
            MixinDuplicableContent
        ],
        props: {
            id: { type: String, required: true },
            modelValue: { type: Object, required: true },
        },
        setup(props, { emit }) {
            return {
                config: props.modelValue.config,
                entity: useModelWrapper(props, emit),
            };
        },
        computed: {
            contentClass() {
                return concat(
                    (this.config.text?.size ?? ''),
                    (this.config.text?.color ?? '')
                ).filter(Boolean);
            },
        }
    }
</script>
