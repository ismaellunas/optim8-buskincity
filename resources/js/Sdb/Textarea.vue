<template>
    <textarea
        class="textarea"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
    >
    </textarea>
</template>

<script>
    export default {
        props: ['modelValue', 'hasError'],
        emits: ['update:modelValue'],
        methods: {
            //focus() {
            //    this.$refs.input.focus()
            //}
        },
    }
</script>
