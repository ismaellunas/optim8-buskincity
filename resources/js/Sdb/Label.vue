<template>
    <label class="label" :class="class">
        <slot></slot>
        <span v-if="isRequired">*</span>
    </label>
</template>

<script>
    export default {
        props: {
            class: {default: []},
            isRequired: Boolean,
        }
    }
</script>
