<template>
    <Editor
        v-model="content"
        :api-key="apiKey"
        :init="editorConfig"
        :disabled="disabled"
    />
</template>

<script>
    import Editor from '@tinymce/tinymce-vue';
    import { textComponent as editorConfig, apiKey } from '@/Libs/tinymce-configs';
    import { useModelWrapper } from '@/Libs/utils'

    export default {
        name: 'BizEditorTinymce',
        components: {
            Editor
        },
        props: {
            modelValue: {},
            config: {
                type: Object
            },
            disabled: {
                type: Boolean,
                default: false
            },
        },
        setup(props, { emit }) {
            return {
                apiKey: apiKey,
                content: useModelWrapper(props, emit),
                editorConfig: props.config ?? editorConfig,
            };
        },
    };
</script>
