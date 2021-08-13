<template>
    <button
        class="button"
        :class="class"
        :type="type"
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
