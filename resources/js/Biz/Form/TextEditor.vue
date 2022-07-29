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
            label: { type: String, default: "" },
            message: { type: Object, default: () => {} },
            placeholder: { type: String, default: ""},
        },

        emits: ['update:modelValue'],

        setup(props, { emit }) {
            return {
                editorValue: useModelWrapper(props, emit),
            };
        },

        data() {
            return {
                editorConfig: {
                    inline: false,
                },
            };
        },
    };
</script>
