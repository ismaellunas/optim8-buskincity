<template>
    <div class="field">
        <biz-label>{{ label }}</biz-label>

        <div class="control">
            <biz-text-editor
                v-model="editorValue"
                :disabled="disabled"
                :placeholder="placeholder"
                :config="config ?? editorConfig"
            />
        </div>

        <biz-input-error :message="message" />
    </div>
</template>

<script>
    import BizInputError from '@/Biz/InputError';
    import BizLabel from '@/Biz/Label';
    import BizTextEditor from '@/Biz/EditorTinymce';
    import { emailConfig } from '@/Libs/tinymce-configs';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'BizFormTextEditor',

        components: {
            BizInputError,
            BizLabel,
            BizTextEditor,
        },

        props: {
            modelValue: { type: [String, null], required: true },
            config: { type: Object, default: () => {} },
            disabled: { type: Boolean, default: false },
            height: { type: [Number, String], default: 300 },
            label: { type: String, default: "" },
            message: { type: Object, default: () => {} },
            mode: {
                type: String,
                default: 'default',
                validator(value) {
                    return ['email', 'default'].includes(value);
                },
            },
            placeholder: { type: String, default: "" },
        },

        emits: ['update:modelValue'],

        setup(props, { emit }) {
            return {
                editorValue: useModelWrapper(props, emit),
            };
        },

        computed: {
            editorConfig() {
                const defaultConfig = {
                    inline: false,
                    height: this.height,
                    placeholder: this.placeholder,
                };

                if (this.mode == 'email') {
                    return Object.assign(defaultConfig, emailConfig);
                }

                return defaultConfig;
            },
        },
    };
</script>
