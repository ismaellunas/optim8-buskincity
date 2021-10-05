<template>

    <sdb-form-input
        v-model="form.name"
        label="Name"
        required
        :message="error('name')"
    ></sdb-form-input>

    <div class="columns is-multiline">
        <div
            v-for="(permissions, groupName) in permissionOptions"
            :key="groupName"
            class="column is-4"
        >
            <label class="label" for="">
                {{ groupName }}
            </label>
            <div class="ml-4">
                <div v-for="permission in permissions"
                    :key="permission.id"
                    class="field is-horizontal"
                >
                    <div class="field-body">
                        <div class="field">
                            <div class="control">
                                <sdb-checkbox
                                    :value=permission.value
                                    v-model:checked="form.permissions"
                                    :disabled="permissionDisabled(permission, permissions)"
                                    @change="onPermissionClicked(permission, permissions)"
                                >
                                    &nbsp; {{ permission.title }}
                                </sdb-checkbox>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import SdbCheckbox from '@/Sdb/Checkbox';
    import SdbFormInput from '@/Sdb/Form/Input';
    import SdbFormSelect from '@/Sdb/Form/Select';
    import { ref } from 'vue';
    import { pull } from 'lodash';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'RoleForm',
        components: {
            SdbCheckbox,
            SdbFormInput,
            SdbFormSelect,
        },
        mixins: [
            MixinHasPageErrors,
        ],
        props: {
            errors: Object,
            modelValue: Object,
            permissionOptions: Object,
        },
        setup(props, { emit }) {
            return {
                form: useModelWrapper(props, emit),
            };
        },
        methods: {
            hasPermission(permission) {
                return this.form.permissions.includes(permission.id);
            },
            permissionDisabled(permission, permissions) {
                if (permission.isAll) {
                    return null;
                } else {
                    const allPermission = permissions.find(function(permission) {
                        return permission.isAll;
                    })

                    if (this.form.permissions.includes(allPermission.value)) {
                        return true;
                    }

                    return null;
                }
            },
            onPermissionClicked(permission, groupedPermissions) {
                const form = this.form;
                if (
                    permission.isAll
                    && form.permissions.includes(permission.value)
                ) {
                    groupedPermissions
                        .filter(groupedPermission => !groupedPermission.isAll)
                        .forEach(function (groupedPermission) {
                            pull(form.permissions, groupedPermission.value);
                        });
                }
            }
        },
    };
</script>
