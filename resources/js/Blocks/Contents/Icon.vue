<template>
    <div :style="dimensionStyle">
        <biz-toolbar-content
            @delete-content="deleteContent"
            @duplicate-content="duplicateContent"
        />

        <div class="contents">
            <div
                class="content"
                :class="config.icon.alignment"
            >
                <span
                    :class="iconClass"
                    :style="iconStyle"
                >
                    <template v-if="config.icon.class !== null">
                        <i :class="config.icon.class" />
                    </template>
                    <template v-else>
                        <i class="empty-icon" />
                    </template>
                </span>
            </div>
        </div>
    </div>
</template>

<script>
    import MixinDeletableContent from '@/Mixins/DeletableContent';
    import MixinContentHasDimension from '@/Mixins/ContentHasDimension';
    import MixinDuplicableContent from '@/Mixins/DuplicableContent';
    import BizToolbarContent from '@/Blocks/Contents/ToolbarContent.vue';
    import { useModelWrapper } from '@/Libs/utils';
    import { concat } from 'lodash';

    export default {
        name: "ContentIcon",

        components: {
            BizToolbarContent,
        },

        mixins: [
            MixinDeletableContent,
            MixinContentHasDimension,
            MixinDuplicableContent,
        ],

        props: {
            id: {type: String, default: null},
            modelValue: {type: Object, required: true},
        },

        setup(props, { emit }) {
            return {
                config: props.modelValue.config,
                entity: useModelWrapper(props, emit),
            };
        },

        computed: {
            iconStyle() {
                const styles = {};

                styles['height'] = 'auto';
                styles['width'] = 'auto';
                styles['font-size'] = this.config.style?.size
                    ? parseInt(this.config.style?.size) + 'px'
                    : null;

                return styles;
            },

            iconClass() {
                return concat(
                    "icon",
                    this.config.icon?.color ?? "",
                ).filter(Boolean);
            }
        },
    }
</script>