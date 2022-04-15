<template>
    <div class="field has-addons">
        <p
            class="control"
            style="width:50%"
        >
            <input
                ref="hex"
                v-model="computedValue"
                class="input"
                maxlength="7"
                :class="{'is-danger': hasError}"
            >
        </p>
        <p
            class="control"
            style="width:50%"
        >
            <input
                ref="color"
                v-model="colorValue"
                class="input p-1"
                type="color"
                :class="{'is-danger': hasError}"
            >
        </p>
    </div>
</template>

<script>
    import { computed } from 'vue';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name:  'BizInputColor',

        props: {
            defaultInvalidHex: {type: String, default: '#ffffff'},
            hasError: {type: Boolean, default: false},
            modelValue: {type: String, required: true},
        },

        emits: ['update:modelValue'],

        setup(props, {emit}) {
            return {
                computedValue: useModelWrapper(props, emit),
                colorValue: computed({
                    get: () => {
                        const regex = /#[0-9A-Fa-f]{6}/g;

                        if (regex.test(props.modelValue)) {
                            return props.modelValue;
                        }

                        return props.defaultInvalidHex;
                    },
                    set: (value) => {
                        emit('update:modelValue', value);
                    }
                })
            }
        },

        methods: {
            focus() {
                this.$refs.hex.focus();
            }
        },
    };
</script>
