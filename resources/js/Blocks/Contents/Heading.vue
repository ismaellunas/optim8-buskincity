<template>
    <div @click="$emit('setting-content', id)" class="page-component">
        <sdb-toolbar-content
            v-if="isEditMode"
            @delete-content="deleteContent"
            @setting-content="$emit('setting-content', id)"
        />

        <template v-if="isEditMode">
            <component :is="headingTag" :class="headingClass">
                <sdb-ckeditor-inline
                    v-model="entity.content.heading.html"
                    :config="editorConfig"
                />
            </component>
        </template>
        <template v-else>
            <component
                :is="headingTag"
                :class="headingClass"
                v-html="entity.content.heading.html"
            />
        </template>
    </div>
</template>

<script>
    import DeletableContentMixin from '@/Mixins/DeletableContent';
    import EditModeContentMixin from '@/Mixins/EditModeContent';
    import SdbCkeditorInline from '@/Sdb/CkeditorInline';
    import SdbToolbarContent from '@/Blocks/Contents/ToolbarContent';
    import { heading as editorConfig } from '@/Libs/ckeditor-configs';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        mixins: [
            EditModeContentMixin,
            DeletableContentMixin
        ],
        components: {
            SdbCkeditorInline,
            SdbToolbarContent,
        },
        props: {
            id: {},
            isEditMode: {type: Boolean, default: false},
            modelValue: {type: Object},
        },
        data() {
            return {
                editorConfig: editorConfig,
            };
        },
        setup(props, { emit }) {
            return {
                entity: useModelWrapper(props, emit),
                config: props.modelValue.config,
            };
        },
        computed: {
            headingTag() {
                return this.config?.tag ?? 'h1';
            },
            headingClass() {
                let classes = [];
                classes.push(this.config?.type ?? 'title');
                classes.push(this.config?.size ?? 'is-1');
                return classes;
            }
        }
    }
</script>
