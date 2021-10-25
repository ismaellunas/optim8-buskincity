<template>
    <sdb-form-password
        v-model="form.password"
        autocomplete="new-password"
        label="Password"
        :message="error('password')"
        :required="true"
    ></sdb-form-password>

    <sdb-form-password
        v-model="form.password_confirmation"
        label="Password Confirmation"
        :message="error('password_confirmation')"
    ></sdb-form-password>
</template>

<script>
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import SdbFormPassword from '@/Sdb/Form/Password';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'UserPasswordForm',
        components: {
            SdbFormPassword,
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
