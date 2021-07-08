<template>
    <textarea
        :class="class"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        class="textarea"
        >
    </textarea>
</template>

<script>
    export default {
        props: ['modelValue', 'class', 'hasError'],
        emits: ['update:modelValue'],
        methods: {
            //focus() {
            //    this.$refs.input.focus()
            //}
        },
    }
</script>
