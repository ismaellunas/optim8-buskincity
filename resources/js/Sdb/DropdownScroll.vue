<template>
    <div
        :style="{ maxHeight: maxHeight + 'px', overflowX: 'auto' }"
    >
        <slot />
    </div>
</template>

<script>
    export default {
        name: "SdbDropdownScroll",

        props: {
            maxHeight: { type: Number, default: 200 }
        },
    }
</script>