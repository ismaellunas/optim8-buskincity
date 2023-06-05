<template>
    <biz-form-textarea
        v-model="computedValue"
        :disabled="schema.is_disabled"
        :label="schema.label"
        :maxlength="schema.maxlength"
        :message="error(schemaName, bagName, errors)"
        :placeholder="schema.placeholder"
        :readonly="schema.is_readonly"
        :required="schema.is_required"
        :rows="schema.rows"
    >
        <template
            v-if="schema.notes.length > 0"
            #note
        >
            <biz-field-notes
                type="info"
                :notes="schema.notes"
            />
        </template>
    </biz-form-textarea>
</template>

<script>
    import BizFieldNotes from '@/Biz/FieldNotes.vue';
    import BizFormTextarea from '@/Biz/Form/Textarea.vue';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'FormTextarea',

        components: {
            BizFieldNotes,
            BizFormTextarea,
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
                type: [String, Number, null],
                default: null
            },
            schema: {
                type: Object,
                required: true
            },
            selectedLocale: {
                type: [String],
                default: null
            },
        },

        setup(props, { emit }) {
            return {
                computedValue: useModelWrapper(props, emit),
            };
        },

        computed: {
            schemaName() {
                let schemaName = this.schema.name;

                if (this.schema.is_translated) {
                    schemaName = schemaName + '.' + this.selectedLocale;
                }

                return schemaName;
            },
        },
    };
</script>
