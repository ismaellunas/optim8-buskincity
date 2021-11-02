<template>
    <sdb-field class="has-addons mb-0">
        <div class="control is-expanded">
            <input
                ref="input"
                v-bind="$attrs"
                class="input"
                :type="type"
                :class="{'is-danger' : hasError}"
                :value="modelValue"
                @input="$emit('update:modelValue', $event.target.value)"
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
</template>

<script>
    import SdbButtonIcon from '@/Sdb/ButtonIcon';
    import SdbField from '@/Sdb/Field';

    export default {
        name: 'SdbInputPassword',

        components: {
            SdbButtonIcon,
            SdbField,
        },

        props: {
            hasError: {type: Boolean, default: false},
            modelValue: {},
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
