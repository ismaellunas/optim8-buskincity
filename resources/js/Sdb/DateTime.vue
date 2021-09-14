<template>
    <bulma-calendar
        ref="datetimepicker"
        :value="modelValue"
        @input="updateDate"
        :options="options"
    />
</template>

<script>
    import BulmaCalendar from "bulma-calendar/dist/components/vue/bulma_calendar";

    export default {
        components: {
            BulmaCalendar,
        },
        props: {
            modelValue: Date|Array|null,
        },
        emits: ['update:modelValue'],
        data() {
            return {
                options: {},
            };
        },
        methods: {
            focus() {
                this.$refs.input.focus()
            },
            updateDate(date) {
                this.$emit('update:modelValue', date);
            }
        },
    };
</script>
