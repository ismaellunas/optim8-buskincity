<template>
    <label class="label">
        <slot />
        <sup
            v-if="isRequired"
            class="has-text-danger"
        >*</sup>

        <span
            v-if="hasLabelAddons"
            class="is-pulled-right"
        >
            <slot name="labelAddons" />
        </span>
    </label>
</template>

<script>
    export default {
        name: 'BizLabel',

        props: {
            isRequired: Boolean,
        },

        computed: {
            hasLabelAddons() {
                return !!this.$slots.labelAddons;
            },
        },
    };
</script>
