<template>
    <div
        :style="dimensionStyle"
        :class="wrapperClass"
    >
        <biz-toolbar-content
            @delete-content="deleteContent"
            @duplicate-content="duplicateContent"
        />

        <a
            class="button"
            :class="buttonClass"
        >
            <biz-icon
                v-if="hasIcon && (config.icon.position === 'left' || config.icon.position === null)"
                :icon="config.icon.class"
            />

            <span
                :class="inputAreaClass"
                contenteditable
                @blur="onEditText($event)"
                v-text="entity.content.button.text"
            />

            <biz-icon
                v-if="hasIcon && config.icon.position === 'right'"
                :icon="config.icon.class"
            />
        </a>
    </div>
</template>

<script>
    import MixinContentHasDimension from '@/Mixins/ContentHasDimension';
    import MixinDeletableContent from '@/Mixins/DeletableContent';
    import MixinDuplicableContent from '@/Mixins/DuplicableContent';
    import fontawesomeAllClasses from '@/Json/fontawesome-all-classes';
    import BizIcon from '@/Biz/Icon.vue';
    import BizToolbarContent from '@/Blocks/Contents/ToolbarContent.vue';
    import { computed } from 'vue';
    import { concat } from 'lodash';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: "ContentButton",

        components: {
            BizToolbarContent,
            BizIcon,
        },

        mixins: [
            MixinContentHasDimension,
            MixinDeletableContent,
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
                iconClasses: fontawesomeAllClasses,
            };
        },

        computed: {
            buttonClass() {
                return concat(
                    (this.config.button.color ?? ''),
                    (this.config.button.isLight ? 'is-light' : ''),
                    (this.config.button.size ?? ''),
                    (this.config.button.width ?? ''),
                    (this.config.button.style ?? ''),
                ).filter(Boolean);
            },

            wrapperClass() {
                return concat(
                    (this.config.button.position ?? '')
                ).filter(Boolean);
            },

            inputAreaClass() {
                return concat(
                    'input-area',
                    (this.config.button.textWeight)
                ).filter(Boolean);
            },

            hasIcon() {
                return !! this.config.icon.class;
            },
        },

        methods: {
            onEditText(evt) {
                this.entity.content.button.text = evt.target.innerText;
            },
        },
    };
</script>
