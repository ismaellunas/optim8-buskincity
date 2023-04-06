<template>
    <div class="mb-4">
        <biz-form-password
            v-model="form.password"
            autocomplete="new-password"
            :label="i18n.password"
            :message="error('password', errorBag)"
            :required="true"
        />

        <biz-form-password
            v-model="form.password_confirmation"
            :label="i18n.password_confirmation"
            :message="error('password_confirmation', errorBag)"
        />
    </div>
</template>

<script>
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import BizFormPassword from '@/Biz/Form/Password.vue';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'UserPasswordForm',

        components: {
            BizFormPassword,
        },

        mixins: [
            MixinHasPageErrors,
        ],

        inject: {
            i18n: { default: () => ({
                password : 'Password',
                password_confirmation : 'Password Confirmation',
            }) },
        },

        props: {
            errorBag: {type: String, default: 'default'},
            modelValue: {},
        },

        setup(props, { emit }) {
            return {
                form: useModelWrapper(props, emit),
            };
        },
    };
</script>
