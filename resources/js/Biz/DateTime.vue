<template>
    <bulma-calendar
        ref="datetimepicker"
        :value="modelValue"
        :options="options"
        @input="$emit('update:modelValue', $event)"
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
