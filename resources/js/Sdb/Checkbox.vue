<template>
    <label
        class="checkbox"
        :class="labelClass"
        :disabled="isLabelDisabled"
    >
        <input
            v-model="proxyChecked"
            type="checkbox"
            :disabled="disabled"
            :value="value"
        >
        <slot />
    </label>
</template>

<script>
    export default {
        name: 'SdbCheckbox',

        props: {
            checked: {
                type: [Array, Boolean],
                default: false,
            },
            disabled: {
                type: Boolean,
                default: false,
            },
            value: {
                type: [String, Number, Boolean, null],
                default: null,
            },
            labelClass: {
                type: [String, Array, Object],
                default: () => []
            },
        },

        emits: ['update:checked'],

        computed: {
            proxyChecked: {
                get() {
                    return this.checked;
                },
                set(val) {
                    this.$emit("update:checked", val);
                },
            },

            isLabelDisabled() {
                return this.disabled ? true : null;
            },
        },
    };
</script>
