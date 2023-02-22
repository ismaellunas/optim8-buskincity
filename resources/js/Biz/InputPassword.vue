<template>
    <biz-field class="has-addons">
        <div class="control is-expanded">
            <input
                ref="input"
                v-bind="$attrs"
                class="input"
                :type="type"
                :class="{'is-danger' : hasError}"
                :value="modelValue"
                @input="$emit('update:modelValue', $event.target.value)"
            >
        </div>

        <div class="control">
            <biz-button-icon
                v-show="isShowPassword"
                :icon="iconEyeSlash"
                type="button"
                tabindex="-1"
                @click="changeTypeInput()"
            />
            <biz-button-icon
                v-show="!isShowPassword"
                :icon="iconEye"
                type="button"
                tabindex="-1"
                @click="changeTypeInput()"
            />
        </div>
    </biz-field>
</template>

<script>
    import BizButtonIcon from '@/Biz/ButtonIcon.vue';
    import BizField from '@/Biz/Field.vue';
    import { eye as iconEye, eyeSlash as iconEyeSlash } from '@/Libs/icon-class';

    export default {
        name: 'BizInputPassword',

        components: {
            BizButtonIcon,
            BizField,
        },

        inheritAttrs: false,

        props: {
            hasError: {type: Boolean, default: false},
            modelValue: {
                type: [String, Number, null],
                required: true
            },
        },

        emits: ['update:modelValue'],

        data() {
            return {
                iconEye,
                iconEyeSlash,
                isShowPassword: false,
                type: 'password',
            };
        },

        methods: {
            focus() {
                this.$refs.input.focus()
            },

            changeTypeInput() {
                this.isShowPassword = !this.isShowPassword;
                if (this.isShowPassword) {
                    this.type = 'text';
                } else {
                    this.type = 'password';
                }
            },
        },
    }
</script>
