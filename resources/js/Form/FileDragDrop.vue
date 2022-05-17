<template>
    <form-file-upload
        ref="file_upload"
        v-model="computedValue"
        :accepted-types="schema.accept"
        :allow-multiple="true"
        :disabled="schema.is_disabled"
        :label="schema.label"
        :max-files="schema.max_file_number"
        :media="schema.media"
        :message="error(schema.name + '.files', bagName, errors)"
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
            <p
                v-if="schema.note"
                class="help"
            >
                {{ schema.note }}
            </p>
        </template>
    </form-file-upload>
</template>

<script>
    import FormFileUpload from '@/Biz/Form/FileUpload';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'FormFileDragDrop',

        components: {
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

        methods: {
            reset() {
                this.$refs.file_upload.reset();
            },
        },
    };
</script>
