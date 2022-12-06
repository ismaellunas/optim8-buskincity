<template>
    <Editor
        v-if="isShowed"
        v-model="content"
        :api-key="apiKey"
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
            config: { type: Object, default: () => {} },
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

        data() {
            return {
                apiKey: null,
                isShowed: false,
            };
        },

        computed: {
            isShowed() {
                return !!this.apiKey;
            },
        },

        mounted() {
            this.getApiKey();
        },

        methods: {
            getApiKey() {
                const self = this;

                axios.get(route('admin.api.tinymce.key'))
                    .then(function (response) {
                        self.apiKey = response.data;
                    })
                    .then(() => self.isShowed = true)
            },
        },
    };
</script>
