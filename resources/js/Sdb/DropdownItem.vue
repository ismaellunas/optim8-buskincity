<template>
    <div
        class="dropdown-item"
        @click="selectItem"
    >
        <slot></slot>
    </div>
</template>

<script>
    export default {
        name: "SdbDropdownItem",
        inject: ['selectItem'],
    };
</script>
