<template>
    <ckeditor
        :config="editorConfig"
        :editor="editor"
        v-model="content"
    />
</template>

<script>
    import InlineEditor from '@ckeditor/ckeditor5-build-inline';
    import { useModelWrapper } from '@/Libs/utils'
    import { common } from '@/Libs/ckeditor-configs';

    export default {
        props: ['modelValue', 'disabledPlugins', 'config'],
        setup(props, { emit }) {
            let config = props.config ?? common;
            return {
                content: useModelWrapper(props, emit),
                editorConfig: config,
            };
        },
        data() {
            return {
                editor: InlineEditor,
            }
        },
    }
</script>
