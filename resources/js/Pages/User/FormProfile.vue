<template>
    <biz-form-input
        v-model="form.first_name"
        label="First Name"
        required
        :message="error('first_name')"
    ></biz-form-input>

    <biz-form-input
        v-model="form.last_name"
        label="Last Name"
        required
        :message="error('last_name')"
    ></biz-form-input>

    <biz-form-input
        v-model="form.email"
        label="Email"
        required
        type="email"
        :message="error('email')"
    ></biz-form-input>

    <biz-form-select
        v-if="canSetRole"
        v-model="form.role"
        label="Role"
        placeholder="- Select a Role -"
        :message="error('role')"
    >
        <option
            v-for="option in roleOptions"
            :key="option.id"
            :value="option.id"
        >
            {{ option.value }}
        </option>
    </biz-form-select>

</template>

<script>
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import BizFormInput from '@/Biz/Form/Input';
    import BizFormSelect from '@/Biz/Form/Select';
    import { ref } from 'vue';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'UserProfileForm',
        components: {
            BizFormInput,
            BizFormSelect,
        },
        mixins: [
            MixinHasPageErrors,
        ],
        props: {
            canSetRole: {type: Boolean, default: true},
            modelValue: {},
            roleOptions: Array,
        },
        setup(props, { emit }) {
            return {
                form: useModelWrapper(props, emit),
            };
        },
    };
</script>
