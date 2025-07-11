<template>
    <div>
        <biz-form-input
            v-model="form.name"
            name="name"
            required
            maxlength="255"
            :disabled="! can?.edit_name"
            :label="i18n.name"
            :message="error('name')"
        />

        <div class="columns is-multiline">
            <div
                v-for="(permissions, groupName) in permissionOptions"
                :key="groupName"
                class="column is-4"
            >
                <label class="label">
                    {{ groupName }}
                </label>
                <div class="ml-4">
                    <div
                        v-for="permission in permissions"
                        :key="permission.id"
                        class="field is-horizontal"
                    >
                        <div class="field-body">
                            <div class="field">
                                <div class="control">
                                    <biz-checkbox
                                        v-model:checked="form.permissions"
                                        :disabled="permissionDisabled(permission, permissions)"
                                        :value="permission.value"
                                        @change="onPermissionClicked(permission, permissions)"
                                    >
                                        &nbsp; {{ permission.title }}
                                    </biz-checkbox>
                                </div>
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
    import BizCheckbox from '@/Biz/Checkbox.vue';
    import BizFormInput from '@/Biz/Form/Input.vue';
    import { pull } from 'lodash';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'RoleForm',

        components: {
            BizCheckbox,
            BizFormInput,
        },

        mixins: [
            MixinHasPageErrors,
        ],

        inject: {
            i18n: { default: () => ({
                name: 'Name',
            }) },
            can: { type: Object, default: () => {} },
        },

        props: {
            errors: { type: Object, default: () => {} },
            modelValue: { type: Object, required: true },
            permissionOptions: { type: Object, default: () => {} },
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

                    if (allPermission && this.form.permissions.includes(allPermission.value)) {
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
