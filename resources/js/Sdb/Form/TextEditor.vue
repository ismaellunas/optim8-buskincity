<template>
    <div class="field">
        <sdb-label>{{ label }}</sdb-label>

        <div class="control">
            <sdb-text-editor
                v-model="editorValue"
                :disabled="disabled"
                :placeholder="placeholder"
                :config="config"
            />
        </div>

        <sdb-input-error :message="message"/>
    </div>
</template>

<script>
    import SdbInputError from '@/Sdb/InputError';
    import SdbLabel from '@/Sdb/Label';
    import SdbTextEditor from '@/Sdb/EditorTinymce';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'SdbFormTextEditor',
        components: {
            SdbInputError,
            SdbLabel,
            SdbTextEditor,
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
