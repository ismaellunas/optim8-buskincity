<template>
    <biz-datepicker
        ref="input"
        v-model="dateValue"
        auto-apply
        keep-action-row
        select-text="Close"
        :cancel-text="null"
        :close-on-auto-apply="false"
        :flow="['calendar', 'time']"
        :month-change-on-scroll="false"
    />
</template>

<script>
    import BizDatepicker from '@/Biz/Datepicker';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'BizDateTime',

        components: {
            BizDatepicker,
        },

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
