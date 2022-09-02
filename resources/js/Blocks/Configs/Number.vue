<template>
    <div>
        <biz-form-number
            v-model="computedValue"
            :label="label"
        />
    </div>
</template>

<script>
    import BizFormNumber from '@/Biz/Form/Number';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'Number',

        components: {
            BizFormNumber,
        },

        props: {
            label: { type: String, default: '' },
            modelValue: { type: [String, Number, null], required: true },
        },

        setup(props, { emit }) {
            return {
                computedValue: useModelWrapper(props, emit),
            };
        },
    }
</script>