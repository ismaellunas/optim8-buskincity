<template>
    <div>
        <biz-toolbar-content @delete-content="deleteContent"/>

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
    import DeletableContentMixin from '@/Mixins/DeletableContent';
    import BizToolbarContent from '@/Blocks/Contents/ToolbarContent';
    import { useModelWrapper } from '@/Libs/utils';
    import { last, concat } from 'lodash';

    export default {
        name: 'Heading',

        mixins: [
            DeletableContentMixin
        ],

        components: {
            BizToolbarContent,
        },

        props: {
            id: String,
            modelValue: {type: Object},
        },

        setup(props, { emit }) {
            return {
                entity: useModelWrapper(props, emit),
                config: props.modelValue.config,
            };
        },

        methods: {
            onEdit(evt){
                this.entity.content.heading.html = evt.target.innerText;
            },
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
                ).filter(Boolean);
            }
        }
    }
</script>
