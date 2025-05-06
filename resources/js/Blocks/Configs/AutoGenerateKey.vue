<template>
    <div>
        <biz-form-key
            v-model="computedValue"
            :label="label"
            :placeholder="settings.placeholder"
            :required="false"
            :is-small="true"
        />
    </div>
</template>

<script>
    import BizFormKey from '@/Biz/Form/Key.vue';
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
                return this.entity[this.settings.generateBasedOn];
            },
        },

        watch: {
            entityGenerated(newValue, oldValue) {
                let value = newValue;
                if (!value) {
                    value = this.entity.type ?? "";
                }

                this.computedValue = convertToKey(value);
            },
        },
    }
</script>