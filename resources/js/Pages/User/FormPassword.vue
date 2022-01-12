<template>
    <biz-form-password
        v-model="form.password"
        autocomplete="new-password"
        label="Password"
        :message="error('password')"
        :required="true"
    ></biz-form-password>

    <biz-form-password
        v-model="form.password_confirmation"
        label="Password Confirmation"
        :message="error('password_confirmation')"
    ></biz-form-password>
</template>

<script>
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import BizFormPassword from '@/Biz/Form/Password';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'UserPasswordForm',
        components: {
            BizFormPassword,
        },
        mixins: [
            MixinHasPageErrors,
        ],
        props: {
            modelValue: {},
        },
        setup(props, { emit }) {
            return {
                form: useModelWrapper(props, emit),
            };
        },
    };
</script>
