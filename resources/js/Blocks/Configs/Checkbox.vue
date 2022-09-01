<template>
    <div>
        <biz-checkbox
            v-model:checked="computedValue"
            class="mb-2"
            :value="true"
            :disabled="isDisabled"
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
            settings: { type: Object, default: () => {} },
            entity: { type: Object, default: () => {} },
        },

        setup(props, { emit }) {
            return {
                computedValue: useModelWrapper(props, emit),
            };
        },

        computed: {
            isDisabled() {
                if (this.settings?.disabledBasedOn) {
                    return this.getValueFromNotation(this.entity, this.settings.disabledBasedOn);
                }

                return false;
            }
        },

        methods: {
            getValueFromNotation(object, notation) {
                if (typeof notation == 'string') {

                    return this.getValueFromNotation(object, notation.split('.'));

                } else if (notation.length == 0) {

                    return object;

                }

                return this.getValueFromNotation(object[notation[0]], notation.slice(1));
            }
        },
    }
</script>