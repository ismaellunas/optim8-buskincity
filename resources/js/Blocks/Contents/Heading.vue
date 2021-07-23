<template>
    <div>
        <sdb-toolbar-content
            v-if="isEditMode"
            @delete-content="deleteContent"
        />

        <template v-if="isEditMode">
            <h1 class="title" :class="wrapperClass">
                <sdb-ckeditor-inline v-model="content.heading.html" :config="editorConfig"/>
            </h1>
        </template>
        <template v-else>
            <div :class="wrapperClass" v-html="content.heading.html"></div>
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
            class: {type: Array},
            id: {},
            isEditMode: {type: Boolean, default: false},
            modelValue: {},
        },
        data() {
            return {
                editorConfig: editorConfig,
            };
        },
        setup(props, { emit }) {
            const content = props.modelValue;
            return {
                content: useModelWrapper(props, emit),
                contentClass: content.heading.attrs.class ?? [],
            };
        },
    }
</script>
