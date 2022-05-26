<template>
    <span class="icon">
        <i :class="icon" />
    </span>
</template>

<script>
    export default {
        name: 'BizIcon',

        props: {
            icon: { type: [Array, Object, String], default: '' },
        },
    };
</script>
