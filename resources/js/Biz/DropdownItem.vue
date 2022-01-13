<template>
    <div
        class="dropdown-item"
        @click="selectItem"
    >
        <slot />
    </div>
</template>

<script>
    export default {
        name: "BizDropdownItem",
        inject: ['selectItem'],
    };
</script>
