<template>
    <sdb-field>
        <sdb-label>{{ label }}</sdb-label>

        <sdb-field class="has-addons mb-0">
            <div class="control is-expanded">
                <sdb-input
                    ref="input"
                    v-bind="$attrs"
                    :type="type"
                    :disabled="disabled"
                    :placeholder="placeholder"
                    :required="required"
                    :value="modelValue"
                    @input="$emit('update:modelValue', $event.target.value)"
                    @keypress="$emit('on-keypress', $event)"
                    @blur="$emit('on-blur', $event)"
                />
            </div>

            <div class="control">
                <sdb-button-icon
                    v-show="isShowPassword"
                    icon="fas fa-eye-slash"
                    type="button"
                    @click="changeTypeInput()"
                />
                <sdb-button-icon
                    v-show="!isShowPassword"
                    icon="fas fa-eye"
                    type="button"
                    @click="changeTypeInput()"
                />
            </div>
        </sdb-field>

        <sdb-input-error :message="message"/>
    </sdb-field>
</template>

<script>
    import SdbButtonIcon from '@/Sdb/ButtonIcon';
    import SdbField from '@/Sdb/Field';
    import SdbInput from '@/Sdb/Input';
    import SdbInputError from '@/Sdb/InputError';
    import SdbLabel from '@/Sdb/Label';

    export default {
        name: 'SdbFormPassword',
        components: {
            SdbButtonIcon,
            SdbField,
            SdbInput,
            SdbInputError,
            SdbLabel,
        },
        inheritAttrs: false,
        props: {
            disabled: {
                type: Boolean,
                default: false
            },
            label: String,
            message: Object|null,
            modelValue: {},
            placeholder: String,
            required: {
                type: Boolean,
                default: false
            },
        },
        emits: [
            'on-blur',
            'on-keypress',
            'update:modelValue',
        ],
        data() {
            return {
                type: 'password',
                isShowPassword: false,
            };
        },
        methods: {
            changeTypeInput() {
                this.isShowPassword = !this.isShowPassword;
                if (this.isShowPassword) {
                    this.type = 'text';
                } else {
                    this.type = 'password';
                }
            },
        },
    };
</script>
