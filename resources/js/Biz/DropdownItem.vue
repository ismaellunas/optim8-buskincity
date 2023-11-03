<template>
    <component
        :is="tag"
        :class="tagClasses"
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
            },
            disabled: {
                type: Boolean,
                default: false,
            },
        },
        computed: {
            tagClasses() {
                return {
                    'dropdown-item': true,
                    'is-clickable': ! this.disabled,
                    'is-cursor-disabled': this.disabled,
                    'is-active': this.isActive,
                    'has-background-light': this.disabled,
                    'has-text-grey-light': this.disabled,
                };
            },
        },
    };
</script>
