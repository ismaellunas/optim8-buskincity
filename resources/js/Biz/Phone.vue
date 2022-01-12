<template>
    <input
        ref="input"
        class="input"
        :class="{'is-danger': hasError}"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        @keypress="keyPress"
    >
</template>

<script>
    export default {
        name: 'BizPhone',

        props: {
            hasError: {type: Boolean, default: false},
            modelValue: { type: [String, Number], default: undefined},
        },

        emits: ['update:modelValue'],

        methods: {
            focus() {
                this.$refs.input.focus()
            },

            keyPress(event) {
                let char = String.fromCharCode(event.keyCode);

                const lastCharacter = event.target.value.slice(-1);

                if ((new RegExp('^[0-9\+]+$')).test(char)) {
                    return true;
                }

                event.preventDefault();
            },
        },
    }
</script>
