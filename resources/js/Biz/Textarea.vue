<template>
    <textarea
        ref="input"
        class="textarea"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
    />
</template>

<script>
    export default {
        name: 'BizTextarea',
        props: ['modelValue', 'hasError'],
        emits: ['update:modelValue'],
        methods: {
            focus() {
                this.$refs.input.focus()
            },
        },
    }
</script>
