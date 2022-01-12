<template>
    <Editor
        v-model="content"
        api-key="gumlypbnhr1ushb1hnnynxp4n6osqza60b3hn7hdtfa6khjb"
        :init="editorConfig"
        :disabled="disabled"
    />
</template>

<script>
    import Editor from '@tinymce/tinymce-vue';
    import { textComponent as editorConfig } from '@/Libs/tinymce-configs';
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
                content: useModelWrapper(props, emit),
                editorConfig: props.config ?? editorConfig,
            };
        },
    };
</script>
