<template>
    <div>
        <sdb-toolbar-content v-if="isEditMode" @delete-content="deleteContent"/>

        <template v-if="isEditMode">
            <component
                :is="headingTag"
                :class="headingClass"
                contenteditable
                @blur="onEdit"
                v-text="entity.content.heading.html"
            >
            </component>
        </template>
        <template v-else>
            <component
                :is="headingTag"
                :class="headingClass"
                v-text="entity.content.heading.html"
            />
        </template>
    </div>
</template>

<script>
    import DeletableContentMixin from '@/Mixins/DeletableContent';
    import EditModeContentMixin from '@/Mixins/EditModeContent';
    import SdbToolbarContent from '@/Blocks/Contents/ToolbarContent';
    import { heading as editorConfig } from '@/Libs/ckeditor-configs';
    import { useModelWrapper } from '@/Libs/utils';
    import { last } from 'lodash';

    export default {
        mixins: [
            EditModeContentMixin,
            DeletableContentMixin
        ],
        components: {
            SdbToolbarContent,
        },
        props: {
            id: {},
            isEditMode: {type: Boolean, default: false},
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
                let classes = [];
                classes.push(this.config.heading?.type ?? 'title');
                classes.push('is-' + last(this.headingTag));
                classes.push(this.config.heading?.alignment ?? "");
                return classes;
            }
        }
    }
</script>
