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

    export default {
        props: ['modelValue', 'disabledPlugins'],
        setup(props, { emit }) {
            return {
                content: useModelWrapper(props, emit),
            };
        },
        data() {
            return {
                editor: InlineEditor,
                editorConfig: {
                    toolbar: [ 'heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', ],
                    heading: {
                        options: [
                            { model: 'paragraph', title: 'Paragraph', class: '' },
                            { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'title is-1' },
                            { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'title is-2' }
                        ]
                    },
                },
            }
        },
    }
</script>
