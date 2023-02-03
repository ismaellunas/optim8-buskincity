<template>
    <span class="icon">
        <i
            aria-hidden="true"
            :class="icon"
        />
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
