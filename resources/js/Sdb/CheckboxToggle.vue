<template>
    <label
        ref="label"
        class="checkbox"
        :disabled="isLabelDisabled"
    >
        <input
            ref="input"
            v-bind="$attrs"
            v-model="computedValue"
            type="checkbox"
            :disabled="disabled"
            :value="value"
            :true-value="trueValue"
            :false-value="falseValue"
            :required="required"
            @click.stop
        >
        <slot />
    </label>
</template>

<script>
    export default {
        name: 'SdbChecboxToggle',

        inheritAttrs: false,

        props: {
            disabled: {
                type: Boolean,
                default: undefined,
            },
            hasError: {
                type: Boolean,
                default: false
            },
            trueValue: {
                type: [String, Number, Boolean],
                default: true,
            },
            falseValue: {
                type: [String, Number, Boolean],
                default: false,
            },
            value: {
                type: [String, Number, Boolean, Function, Object, Array],
                default: undefined
            },
            required: {
                type: Boolean,
                default: false
            },
            modelValue: {
                type: [String, Number, Boolean, null],
                required: true
            },
        },

        emits: [
            'input',
        ],

        data() {
            return {
                newValue: this.value,
            };
        },

        computed: {
            computedValue: {
                get() {
                    return this.value;
                },
                set(value) {
                    this.newValue = value;
                    this.$emit('input', value);
                }
            },

            isLabelDisabled() {
                return this.disabled ? true : null;
            },
        },

        watch: {
            value(value) {
                this.newValue = value
            }
        },
    };
</script>
