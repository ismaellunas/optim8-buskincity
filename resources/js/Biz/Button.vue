<template>
    <button
        class="button"
        :type="type"
        >
        <slot></slot>
    </button>
</template>

<script>
    export default {
        name: 'BizButton',
        props: {
            type: {
                type: String,
                default: 'submit',
            },
        }
    }
</script>
