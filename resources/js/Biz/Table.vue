<template>
    <table
        class="table"
        :class="{ 'is-fullwidth': isFullwidth }"
    >
        <slot />
    </table>
</template>

<script>
    export default {
        name: 'BizTable',

        props: {
            isFullwidth: { type: Boolean, default: false },
        },
    };
</script>
