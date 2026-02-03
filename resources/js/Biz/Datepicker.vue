<template>
    <Datepicker
        v-model="dates"
        v-bind="$attrs"
    />
</template>

<script>
    import Datepicker from '@vuepic/vue-datepicker';
    import '@vuepic/vue-datepicker/dist/main.css';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'BizDatepicker',

        components: {
            Datepicker
        },

        inheritAttrs: false,

        props: {
            modelValue: { type: [String, Array, Date, null], required: true },
        },

        setup(props, { emit }) {
            return {
                dates: useModelWrapper(props, emit),
            };
        },
    };
</script>
