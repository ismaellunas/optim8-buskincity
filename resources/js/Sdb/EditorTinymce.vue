<template>
    <editor
        v-model="content"
        api-key="gumlypbnhr1ushb1hnnynxp4n6osqza60b3hn7hdtfa6khjb"
        :init="editorConfig"
    >
    </editor>
</template>

<script>
    import Editor from '@tinymce/tinymce-vue';
    import { textComponent as editorConfig } from '@/Libs/tinymce-configs';
    import { useModelWrapper } from '@/Libs/utils'

    export default {
        components: {
            editor: Editor
        },
        props: ['modelValue', 'config'],
        setup(props, { emit }) {
            return {
                content: useModelWrapper(props, emit),
                editorConfig: props.config ?? editorConfig,
            };
        },
    }
</script>
