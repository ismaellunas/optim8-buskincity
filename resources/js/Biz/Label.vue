<template>
    <label class="label">
        <slot />
        <span v-if="isRequired">*</span>
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
