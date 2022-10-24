<template>
    <div :style="dimensionStyle">
        <biz-toolbar-content
            @delete-content="deleteContent"
            @duplicate-content="duplicateContent"
        />

        <component
            :is="headingTag"
            :class="headingClass"
            contenteditable
            @blur="onEdit"
            v-text="entity.content.heading.html"
        />
    </div>
</template>

<script>
    import MixinContentHasDimension from '@/Mixins/ContentHasDimension';
    import MixinDeletableContent from '@/Mixins/DeletableContent';
    import MixinDuplicableContent from '@/Mixins/DuplicableContent';
    import BizToolbarContent from '@/Blocks/Contents/ToolbarContent';
    import { useModelWrapper } from '@/Libs/utils';
    import { last, concat } from 'lodash';

    export default {
        name: 'Heading',

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
                entity: useModelWrapper(props, emit),
                config: props.modelValue.config,
            };
        },

        computed: {
            headingTag() {
                return this.config.heading?.tag ?? 'h1';
            },

            headingClass() {
                return concat(
                    this.config.heading?.type ?? "title",
                    'is-' + last(this.headingTag),
                    this.config.heading?.alignment ?? "",
                    this.config.heading?.color ?? "",
                ).filter(Boolean);
            }
        },

        methods: {
            onEdit(evt){
                this.entity.content.heading.html = evt.target.innerText;
            },
        },
    }
</script>
