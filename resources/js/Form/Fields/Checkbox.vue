<template>
    <biz-form-checbox-toggle
        v-model="computedValue"
        :false-value="schema.false_value"
        :true-value="schema.true_value"
        :text="schema.text"
        :disabled="schema.is_disabled"
        :is-raw="schema.is_raw"
        :message="error(schema.name, bagName, errors)"
        :readonly="schema.is_readonly"
        :required="schema.is_required"
        :value="schema.value"
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
    </biz-form-checbox-toggle>
</template>

<script>
    import BizFieldNotes from '@/Biz/FieldNotes.vue';
    import BizFormChecboxToggle from '@/Biz/Form/CheckboxToggle.vue';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'FormCheckbox',

        components: {
            BizFieldNotes,
            BizFormChecboxToggle,
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
                type: [String, Number, Boolean, null],
                required: true
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
    };
</script>
