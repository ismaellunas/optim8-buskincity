<template>
    <label class="label">
        <slot />
        <sup
            v-if="isRequired"
            class="has-text-danger"
        >*</sup>
    </label>
</template>

<script>
    export default {
        name: 'BizLabel',

        props: {
            isRequired: Boolean,
        }
    };
</script>
