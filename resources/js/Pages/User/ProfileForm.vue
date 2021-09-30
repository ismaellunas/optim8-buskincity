<template>
    <sdb-form-input
        v-model="form.name"
        label="Name"
        required
        :message="error('name')"
    ></sdb-form-input>

    <sdb-form-input
        v-model="form.email"
        label="Email"
        required
        :message="error('email')"
    ></sdb-form-input>

    <sdb-form-select
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
    </sdb-form-select>

</template>

<script>
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import SdbFormInput from '@/Sdb/Form/Input';
    import SdbFormSelect from '@/Sdb/Form/Select';
    import { ref } from 'vue';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'UserForm',
        components: {
            SdbFormInput,
            SdbFormSelect,
        },
        mixins: [
            MixinHasPageErrors,
        ],
        props: {
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
