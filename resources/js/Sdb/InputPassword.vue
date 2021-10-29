<template>
    <input
        ref="input"
        class="input"
        :type="type"
        :class="{'is-danger' : hasError}"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
    />
</template>

<script>
    export default {
        name: 'SdbInputPassword',

        props: {
            hasError: {type: Boolean, default: false},
            modelValue: {},
            type: {type: String, default: 'password'},
        },

        emits: ['update:modelValue'],

        methods: {
            focus() {
                this.$refs.input.focus()
            }
        },
    }
</script>
