<template>
    <biz-datepicker
        ref="input"
        v-model="dateValue"
        auto-apply
        keep-action-row
        select-text="Select"
        :cancel-text="null"
        :close-on-auto-apply="false"
        :flow="['calendar', 'time']"
        :month-change-on-scroll="false"
        :action-row="{showCancel: false}"
        v-bind="$attrs"
    />
</template>

<script>
    import BizDatepicker from '@/Biz/Datepicker.vue';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'BizDateTime',

        components: {
            BizDatepicker,
        },

        inheritAttrs: false,

        props: {
            modelValue: {
                type: [String, Date, Array, null],
                required: true,
            },
        },

        emits: [
            'update:modelValue',
        ],

        setup(props, { emit }) {
            return {
                dateValue: useModelWrapper(props, emit),
            };
        },

        methods: {
            focus() {
                this.$refs.input.focus();
            },
        },
    };
</script>
