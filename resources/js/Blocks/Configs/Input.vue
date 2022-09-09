<template>
    <div>
        <biz-form-input
            v-model="computedValue"
            :label="label"
        />
    </div>
</template>

<script>
    import BizFormInput from '@/Biz/Form/Input';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'Input',

        components: {
            BizFormInput,
        },

        props: {
            label: { type: String, default: '' },
            modelValue: { type: [String, null], required: true },
        },

        setup(props, { emit }) {
            return {
                computedValue: useModelWrapper(props, emit),
            };
        },
    }
</script>