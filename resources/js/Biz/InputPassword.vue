<template>
    <biz-field class="has-addons mb-0">
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
                icon="fas fa-eye-slash"
                type="button"
                @click="changeTypeInput()"
            />
            <biz-button-icon
                v-show="!isShowPassword"
                icon="fas fa-eye"
                type="button"
                @click="changeTypeInput()"
            />
        </div>
    </biz-field>
</template>

<script>
    import BizButtonIcon from '@/Biz/ButtonIcon';
    import BizField from '@/Biz/Field';

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
                type: 'password',
                isShowPassword: false,
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
