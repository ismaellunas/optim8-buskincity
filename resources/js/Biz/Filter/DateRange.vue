<template>
    <biz-datepicker
        v-model="rawDates"
        input-class-name="input"
        range
        :enable-time-picker="false"
        @update:model-value="onUpdate"
        @cleared="clearDates"
    />
</template>

<script>
    import BizDatepicker from '@/Biz/Datepicker';
    import moment from 'moment';
    import { ref } from "vue";
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: "BizFilterDateRange",

        components: {
            BizDatepicker,
        },

        props: {
            modelValue: { type: [String, Array, Date, null], required: true },
        },

        emits: [
            'update:modelValue',
        ],

        setup(props, { emit }) {
            return {
                dates: useModelWrapper(props, emit),
                rawDates: ref(props.modelValue),
            };
        },

        methods: {
            onUpdate(rawDates) {
                if (Array.isArray(rawDates)) {
                    const dates = rawDates.map((date) => {
                        if (date) {
                            return moment(date).format('YYYY-MM-DD');
                        }
                        return date;
                    });

                    this.dates = dates;
                }
            },

            clearDates() {
                this.dates = [];
            },
        },
    };
</script>
