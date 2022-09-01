<template>
    <div>
        <biz-form-key
            v-model="computedValue"
            :label="label"
        />
    </div>
</template>

<script>
    import BizFormKey from '@/Biz/Form/Key';
    import { useModelWrapper, convertToKey } from '@/Libs/utils';

    export default {
        name: 'AutoGenerateKey',

        components: {
            BizFormKey,
        },

        props: {
            label: { type: String, default: '' },
            modelValue: { type: [String, null], required: true },
            settings: { type: Object, default: () => {} },
            entity: { type: Object, default: () => {} },
        },

        setup(props, { emit }) {
            return {
                computedValue: useModelWrapper(props, emit),
            };
        },

        computed: {
            entityGenerated() {
                return this.entity[this.settings.generateBy];
            },
        },

        watch: {
            entityGenerated(newValue, oldValue) {
                this.computedValue = convertToKey(newValue);
            },
        },
    }
</script>