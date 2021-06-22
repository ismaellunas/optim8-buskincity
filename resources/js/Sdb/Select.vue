<template>
    <div class="select">
        <select
            :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)"
            ref="input"
            >
            <slot></slot>
        </select>
    </div>
</template>

<script>
    export default {
        props: ['modelValue'],

        emits: ['update:modelValue'],

        methods: {
            focus() {
                this.$refs.input.focus()
            }
        }
    }
</script>
