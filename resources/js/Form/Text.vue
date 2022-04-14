<template>
    <form-input
        v-model="computedValue"
        :label="schema.label"
        :maxlength="schema.maxlength"
        :placeholder="schema.placeholder"
        :disabled="schema.is_disabled"
        :readonly="schema.is_readonly"
        :required="schema.is_required"
        :message="error(schemaName, bagName, errors)"
    >
        <template #note>
            <p
                v-if="schema.note"
                class="help"
            >
                {{ schema.note }}
            </p>
        </template>
    </form-input>
</template>

<script>
    import FormInput from '@/Biz/Form/Input';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'FormText',

        components: {
            FormInput,
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
