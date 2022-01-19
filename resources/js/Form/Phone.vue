<template>
    <biz-form-phone
        v-model="computedValue"
        :label="schema.label"
        :maxlength="schema.maxlength"
        :placeholder="schema.placeholder"
        :disabled="schema.is_disabled"
        :readonly="schema.is_readonly"
        :required="schema.is_required"
        :message="message"
        :country-options="schema.countryOptions"
        :default-country="schema.defaultCountry"
    />
</template>

<script>
    import BizFormPhone from '@/Biz/Form/Phone';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'FormPhone',

        components: {
            BizFormPhone,
        },

        props: {
            schema: {
                type: Object,
                required: true
            },
            modelValue: {
                type: Object,
                required: true
            },
            message: {
                type: [Object, String, Array],
                default: undefined
            },
        },

        setup(props, { emit }) {
            return {
                computedValue: useModelWrapper(props, emit),
            };
        },
    };
</script>
