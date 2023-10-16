<template>
    <label class="label">
        <slot />

        <sup
            v-if="isRequired"
            class="has-text-danger"
        >*</sup>

        <slot name="tooltip" />
    </label>
</template>

<script>
    export default {
        name: 'BizLabel',

        props: {
            isRequired: Boolean,
        },
    };
</script>