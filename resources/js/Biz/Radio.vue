<template>
    <label
        ref="label"
        class="radio"
        :class="[labelClass, { 'is-disabled': disabled }]"
        :disabled="isLabelDisabled"
        @click="focus"
        @keydown.prevent.enter="$refs.label.click()"
    >
        <input
            ref="input"
            v-bind="$attrs"
            v-model="computedValue"
            type="radio"
            :checked="isChecked"
            :disabled="disabled"
            :value="value"
            @click.stop
        >
        <slot />
    </label>
</template>

<script>
    export default {
        name: 'BizRadio',

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
            labelClass: {
                type: [String, Object, Array],
                default: undefined,
            },
            modelValue: {
                type: [String, Number, Boolean, null],
                required: true
            },
            value: {
                type: [String, Number, Boolean, Function, Object, Array],
                default: undefined
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
            isChecked() {
                return this.modelValue == this.value;
            },

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

        methods: {
            focus() {
                this.$refs.input.focus()
            }
        },
    };
</script>
