<template>
    <bulma-calendar
        ref="datetimepicker"
        :value="modelValue"
        :options="options"
        @input="onInput"
    />
</template>

<script>
    import BulmaCalendar from "bulma-calendar/dist/components/vue/bulma_calendar";

    export default {
        name: 'BizDateTime',

        components: {
            BulmaCalendar,
        },

        props: {
            modelValue: {
                type: [Date, Array, null],
                required: true,
                default: null,
            },
            options: {
                type: Object,
                default: () => {}
            }
        },

        emits: [
            'update:modelValue',
            'input',
        ],

        computed: {
            isRange() {
                return this.$refs.datetimepicker.range;
            },

            isTimeType() {
                return this.$refs.datetimepicker.type == 'time';
            },
        },

        methods: {
            focus() {
                this.$refs.input.focus()
            },

            onInput(date, event) {
                if (this.isRange && this.isTimeType) {
                    date = this.emitTimeRange(date);
                }

                this.$emit('update:modelValue', date, event);
                this.$emit('input', date, event);
            },

            emitTimeRange(date) {
                const timeRanges = this.$refs.datetimepicker.$refs.cal.value.split(' - ');

                timeRanges.forEach((timeRange, index) => {
                    const timeParts = timeRange.split(':');

                    date[index].setHours(timeParts[0]);
                    date[index].setMinutes(timeParts[1]);
                });

                return date;
            },
        },
    };
</script>
