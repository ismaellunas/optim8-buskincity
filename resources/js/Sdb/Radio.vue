<template>
    <label class="radio">
        <input
            ref="input"
            v-bind="$attrs"
            v-model="computedValue"
            type="radio"
            :checked="isChecked"
            :class="{'is-danger' : hasError}"
            :value="value"
            @input="$emit('update:modelValue', $event.target.value)"
        >
        <slot />
    </label>
</template>

<script>
    export default {
        name: 'SdbRadio',

        inheritAttrs: false,

        props: {
            hasError: {type: Boolean, default: false},
            modelValue: {type: [String, Number, Boolean, null], default: ""},
            value: { type: [String, Number, Boolean, null], default: undefined },
        },

        emits: [
            'input',
            'update:modelValue'
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
            }
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
