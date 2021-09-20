<template>
    <bulma-calendar
        ref="datetimepicker"
        :value="modelValue"
        @input="$emit('update:modelValue', $event)"
        :options="options"
    />
</template>

<script>
    import BulmaCalendar from "bulma-calendar/dist/components/vue/bulma_calendar";

    export default {
        name: 'SdbDateTime',
        components: {
            BulmaCalendar,
        },
        props: {
            modelValue: Date|Array|null,
            options: {
                type: Object,
                default: {}
            }
        },
        emits: ['update:modelValue'],
        methods: {
            focus() {
                this.$refs.input.focus()
            },
        },
    };
</script>
