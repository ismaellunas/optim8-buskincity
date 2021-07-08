<template>
    <button
        :class="class"
        :type="type"
        class="button"
        >
        <slot></slot>
    </button>
</template>

<script>
    export default {
        props: {
            type: {
                type: String,
                default: 'submit',
            },
            class: {
                default: ['is-primary'],
            },
        }
    }
</script>
