<template>
    <input
        ref="input"
        class="input"
        :class="{'is-danger' : hasError}"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
    >
</template>

<script>
    export default {
        name: 'BizInput',

        props: {
            hasError: {type: Boolean, default: false},
            modelValue: { type: [String, Number, null], default: null },
        },

        emits: ['update:modelValue'],

        methods: {
            focus() {
                this.$refs.input.focus()
            }
        },
    }
</script>
