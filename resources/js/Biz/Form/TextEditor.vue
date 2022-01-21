<template>
    <div class="field">
        <biz-label>{{ label }}</biz-label>

        <div class="control">
            <biz-text-editor
                v-model="editorValue"
                :disabled="disabled"
                :placeholder="placeholder"
                :config="config"
            />
        </div>

        <biz-input-error :message="message"/>
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
        emits: ['update:modelValue'],
        setup(props, { emit }) {
            return {
                editorValue: useModelWrapper(props, emit),
            };
        },
        props: {
            config: Object,
            disabled: {
                type: Boolean,
                default: false
            },
            label: String,
            message: Object,
            modelValue: {},
            placeholder: String,
        }
    };
</script>
