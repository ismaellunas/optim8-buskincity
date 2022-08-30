<template>
    <div>
        <biz-checkbox
            v-model:checked="computedValue"
            :value="true"
            class="mb-2"
        >
            <span class="ml-2">
                {{ label }}
            </span>
        </biz-checkbox>
    </div>
</template>

<script>
    import BizCheckbox from '@/Biz/Checkbox';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'Checkbox',

        components: {
            BizCheckbox,
        },

        props: {
            label: { type: String, default: '' },
            modelValue: { type: [Array, Boolean, null], required: true },
        },

        setup(props, { emit }) {
            return {
                computedValue: useModelWrapper(props, emit),
            };
        },
    }
</script>