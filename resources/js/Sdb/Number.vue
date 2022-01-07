<template>
    <input
        ref="input"
        class="input"
        :class="{'is-danger': hasError}"
        :value="modelValue"
        :type="type"
        @input="$emit('update:modelValue', $event.target.value)"
        @keypress="keyPress"
    >
</template>

<script>
    export default {
        name: 'SdbNumber',

        props: {
            hasError: {type: Boolean, default: false},
            modelValue: { type: [String, Number], default: undefined},
            type: { type: String, default: 'number' },
        },

        emits: ['update:modelValue'],

        methods: {
            focus() {
                this.$refs.input.focus()
            },

            keyPress(event) {
                let char = String.fromCharCode(event.keyCode);

                if ((new RegExp('^[0-9,.]+$')).test(char)) {
                    return true;
                }

                event.preventDefault();
            },
        },
    }
</script>
