<template>
    <biz-card
        ref="card"
        :is-collapsed="true"
        :is-expanding-on-load="isExpandingOnLoad"
        @on-click-header-card="onClickHeaderCard"
    >
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
    </biz-card>
</template>

<script>
    import BizFormSelect from '@/Biz/Form/Select';
    import BizCard from '@/Biz/Card';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        components: {
            BizFormSelect,
            BizCard,
        },

        props: {
            modelValue: {type: [Object, Array], default: () => {}},
            isExpandingOnLoad: { type: Boolean, default: false },
        },

        emits: [
            'on-click-header-card',
        ],

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
                    { value: "is-small", name: "Small" },
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

        methods: {
            onClickHeaderCard(isContentShown) {
                this.$emit('on-click-header-card', isContentShown);
            },
        },
    };
</script>
