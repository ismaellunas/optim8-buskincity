<template>
    <label
        class="checkbox"
        :disabled=disabled
    >
        <input
            v-model="proxyChecked"
            type="checkbox"
            :disabled=disabled
            :value="value"
        />
        <slot></slot>
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
                default: null,
            },
            value: {
                default: null,
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
        },
    };
</script>
