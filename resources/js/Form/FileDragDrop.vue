<template>
    <form-file-upload
        ref="file_upload"
        v-model="computedValue"
        :accepted-types="schema.accept"
        :allow-multiple="true"
        :disabled="schema.is_disabled"
        :label="schema.label"
        :max-files="schema.max_file_number"
        :max-file-size="schema.max_file_size"
        :media="schema.media"
        :message="error(schema.name + '.files', bagName, errors)"
        :placeholder="schema.placeholder"
        :readonly="schema.is_readonly"
        :required="schema.is_required"
        :selected-files="selectedFiles"
    >
        <template #note>
            <biz-field-notes
                type="info"
                :notes="notes"
            />
        </template>
    </form-file-upload>
</template>

<script>
    import BizFieldNotes from '@/Biz/FieldNotes.vue';
    import FormFileUpload from '@/Biz/Form/FileUpload.vue';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'FormFileDragDrop',

        components: {
            BizFieldNotes,
            FormFileUpload,
        },

        mixins: [
            MixinHasPageErrors,
        ],

        inject: [
            'bagName',
        ],

        props: {
            errors: {
                type: Object,
                default: () => {}
            },
            modelValue: {
                type: [Object, null],
                default: null
            },
            schema: {
                type: Object,
                required: true
            },
        },

        setup(props, { emit }) {
            return {
                computedValue: useModelWrapper(props, emit),
            };
        },

        data() {
            return {
                selectedFiles: [],
            };
        },

        computed: {
            notes() {
                return this.schema.instructions
                    .concat(this.schema.notes);
            },
        },

        methods: {
            reset() {
                this.$refs.file_upload.reset();
            },
        },
    };
</script>
