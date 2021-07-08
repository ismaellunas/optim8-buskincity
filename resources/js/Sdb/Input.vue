<template>
    <input class="input"
        :class="{'is-danger' : hasError}"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        ref="input"
    />
</template>

<script>
    export default {
        props: {
            modelValue: {},
            hasError: {default: false}
        },

        emits: ['update:modelValue'],

        methods: {
            //focus() {
            //    this.$refs.input.focus()
            //}
        },
    }
</script>
