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
    import { get } from 'lodash';

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
                if (this.settings?.disableBasedOn) {
                    return get(this.entity, this.settings.disableBasedOn);
                }

                return false;
            }
        },

        watch: {
            isDisabled(newData, oldData) {
                if (newData) {
                    this.computedValue = !newData;
                }
            }
        },
    }
</script>