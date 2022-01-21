<template>
    <component
        :is="tag"
        :class="{'dropdown-item': true, 'is-active': isActive}"
        @click="selectItem"
    >
        <slot />
    </component>
</template>

<script>
    export default {
        name: "BizDropdownItem",
        inject: ['selectItem'],
        props: {
            tag: {
                type: String,
                default: 'div'
            },
            isActive: {
                type: Boolean,
                default: false,
            }
        },
    };
</script>
