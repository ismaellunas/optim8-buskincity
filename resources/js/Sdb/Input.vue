<template>
    <input
        ref="input"
        class="input"
        :class="{'is-danger' : hasError}"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
    />
</template>

<script>
    export default {
        props: {
            hasError: {type: Boolean, default: false},
            modelValue: {},
        },

        emits: ['update:modelValue'],

        methods: {
            focus() {
                this.$refs.input.focus()
            }
        },
    }
</script>
