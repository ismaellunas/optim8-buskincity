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
            <span class="icon-text">
                <span
                    class="icon"
                >
                    <template v-if="config.icon.class !== null">
                        <i :class="config.icon.class" />
                    </template>
                    <template v-else>
                        <i class="empty-icon" />
                    </template>
                </span>

                <div
                    contenteditable
                    :style="textStyle"
                    @blur="onEdit"
                    v-text="entity.content.text"
                />
            </span>
        </div>
    </div>
</template>

<script>
    import MixinContentHasDimension from '@/Mixins/ContentHasDimension';
    import MixinDeletableContent from '@/Mixins/DeletableContent';
    import MixinDuplicableContent from '@/Mixins/DuplicableContent';
    import BizToolbarContent from '@/Blocks/Contents/ToolbarContent';
    import { concat } from 'lodash';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'IconText',

        components: {
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
                    (this.config.text?.alignment ?? ''),
                    (this.config.text?.size ?? ''),
                    (this.config.text?.color ?? ''),
                    (this.config.text?.weight ?? '')
                ).filter(Boolean);
            },

            textStyle() {
                return {
                    'border-bottom': '1px solid #ccc',
                    'min-width': '100px',
                    'min-height': '30px',
                }
            },
        },

        methods: {
            onEdit(evt){
                this.entity.content.text = evt.target.innerText;
            },
        },
    }
</script>
