<template>
    <div class="field has-addons">
        <p
            class="control"
            style="width:50%"
        >
            <input
                ref="hex"
                class="input"
                maxlength="7"
                :class="{'is-danger': hasError}"
                :value="modelValue"
                @input="onInput"
            >
        </p>
        <p
            class="control"
            style="width:50%"
        >
            <input
                ref="color"
                class="input p-1"
                type="color"
                :class="{'is-danger': hasError}"
                :value="modelValue"
                @input="onInput"
            >
        </p>
    </div>
</template>

<script>
    export default {
        name:  'BizInputColor',

        props: {
            hasError: {type: Boolean, default: false},
            modelValue: {},
        },

        emits: ['update:modelValue'],

        methods: {
            onInput(event) {
                this.$emit('update:modelValue', event.target.value);
            },
            focus() {
                this.$refs.hex.focus();
            }
        },
    };
</script>
