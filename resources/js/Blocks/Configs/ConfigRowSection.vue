<template>
    <card>
        <template #headerTitle>
            Section
        </template>

        <biz-form-select
            v-if="computedValue.hasOwnProperty('isIncluded')"
            v-model="computedValue.isIncluded"
            label="Is Section Included?"
        >
            <option
                v-for="(option, index) in toggleOptions"
                :key="index"
                :value="option.value"
            >
                {{ option.name }}
            </option>
        </biz-form-select>

        <fieldset :disabled="isSectionDisabled">
            <biz-form-select
                v-if="computedValue.hasOwnProperty('size')"
                v-model="computedValue.size"
                label="Size"
            >
                <option
                    v-for="(option, index) in sizeOptions"
                    :key="index"
                    :value="option.value"
                >
                    {{ option.name }}
                </option>
            </biz-form-select>
        </fieldset>
    </card>
</template>

<script>
    import BizFormSelect from '@/Biz/Form/Select';
    import Card from '@/Biz/Card';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        components: {
            BizFormSelect,
            Card,
        },

        props: {
            modelValue: {type: [Object, Array], default: () => {}},
        },

        setup(props, {emit}) {
            return {
                computedValue: useModelWrapper(props, emit),
            };
        },

        data() {
            return {
                toggleOptions: [
                    { value: false, name: "No" },
                    { value: true, name: "Yes" },
                ],
                sizeOptions: [
                    { value: null, name: "Normal" },
                    { value: "is-medium", name: "Medium" },
                    { value: "is-large", name: "Large" },
                ],
            };
        },

        computed: {
            isSectionDisabled() {
                if (this.computedValue?.isIncluded) {
                    return false;
                }
                return true;
            },
        },
    };
</script>
