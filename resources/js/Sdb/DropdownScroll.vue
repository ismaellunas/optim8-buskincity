<template>
    <div id="dropdown-scroll">
        <slot />
    </div>
</template>

<script>
    export default {
        name: "SdbDropdownScroll"
    }
</script>

<style scoped>
    #dropdown-scroll {
        max-height: 300px;
        overflow-x: auto;
    }
</style>