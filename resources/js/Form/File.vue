<template>
    <form-field-files
        ref="field_files"
        v-model="computedValue"
        :accepted-types="schema.accepted_files"
        :disabled="schema.is_disabled"
        :file-messages="fileMessages"
        :label="schema.label"
        :max-file-number="schema.max_file_number"
        :media="schema.media"
        :message="error(schema.name, bagName, errors)"
        :placeholder="schema.placeholder"
        :readonly="schema.is_readonly"
        :required="schema.is_required"
        :selected-files="selectedFiles"
    >
        <template #note>
            <p class="help is-info">
                <ul>
                    <li
                        v-for="instruction, index in schema.instructions"
                        :key="index"
                    >
                        {{ instruction }}
                    </li>
                </ul>
            </p>
        </template>
    </form-field-files>
</template>

<script>
    import FormFieldFiles from '@/Biz/Form/FieldFiles';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'FormFile',

        components: {
            FormFieldFiles,
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
            fileMessages() {
                const self = this;

                let fileMessages = [];

                self.computedValue.files.forEach((file, index) => {
                    fileMessages.push(
                        self.error(
                            self.schema.name + '.files.' + index,
                            self.bagName,
                            self.errors
                        )
                    );
                });

                return fileMessages;
            },
        },

        methods: {
            reset() {
                this.$refs.field_files.reset();
            },
        },
    };
</script>
