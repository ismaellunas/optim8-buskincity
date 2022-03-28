<template>
    <div class="mb-4">
        <biz-form-password
            v-model="form.password"
            autocomplete="new-password"
            label="Password"
            :message="error('password', errorBag)"
            :required="true"
        />

        <biz-form-password
            v-model="form.password_confirmation"
            label="Password Confirmation"
            :message="error('password_confirmation', errorBag)"
        />
    </div>
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
