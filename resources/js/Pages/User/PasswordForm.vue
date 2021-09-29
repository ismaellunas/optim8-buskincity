<template>
    <sdb-form-input
        v-model="form.password"
        autocomplete="new-password"
        label="Password"
        type="password"
        :message="error('password')"
    ></sdb-form-input>

    <sdb-form-input
        v-model="form.password_confirmation"
        label="Password Confirmation"
        type="password"
        :message="error('password_confirmation')"
    ></sdb-form-input>
</template>

<script>
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import SdbFormInput from '@/Sdb/Form/Input';
    import SdbFormSelect from '@/Sdb/Form/Select';
    import { ref } from 'vue';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'PasswordForm',
        components: {
            SdbFormInput,
            SdbFormSelect,
        },
        mixins: [
            MixinHasPageErrors,
        ],
        props: {
            errors: {},
            isProcessing: Boolean,
            modelValue: {},
        },
        setup(props, { emit }) {
            return {
                form: useModelWrapper(props, emit),
            };
        },
    };
</script>
