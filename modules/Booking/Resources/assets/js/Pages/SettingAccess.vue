<template>
    <form
        id="access-form"
        method="post"
        @submit.prevent="$emit('submit')"
    >
        <div class="columns is-multiline">
            <div class="column is-12">
                <biz-form-checkbox-toggle
                    v-model="form.access_common_user"
                    :text="i18n.access_common_user"
                    :value="form.access_common_user"
                />
            </div>

            <div class="column is-12">
                <biz-form-select
                    v-model="selectedUser"
                    :label="i18n.choose_roles"
                    @change="onSelect()"
                >
                    <template v-if="hasRoleOptions">
                        <option
                            v-for="(option, index) in filteredRoleOptions"
                            :key="index"
                            :value="option.id"
                        >
                            {{ option.value }}
                        </option>
                    </template>

                    <template v-else>
                        <option :value="null">
                            {{ i18n.empty }}
                        </option>
                    </template>

                    <template #note>
                        <p class="help has-text-info">
                            {{ i18n.choose_roles_note }}
                        </p>
                    </template>
                </biz-form-select>
            </div>

            <div
                v-if="hasSelectedRoles"
                class="column is-12"
            >
                <div class="box">
                    <biz-tag
                        v-for="(roleId, index) in form.access_roles"
                        :key="index"
                        class="is-medium mx-1"
                    >
                        {{ roleName(roleId) }}

                        <button
                            type="button"
                            class="delete is-small"
                            @click="removeSelectedRole(index)"
                        />
                    </biz-tag>
                </div>
            </div>
        </div>

        <div class="field is-grouped is-grouped-right mt-4">
            <div class="control">
                <biz-button class="is-link">
                    {{ i18n.save }}
                </biz-button>
            </div>
        </div>
    </form>
</template>

<script>
    import MixinHasTranslation from '@/Mixins/HasTranslation';
    import BizButton from '@/Biz/Button.vue';
    import BizFormCheckboxToggle from '@/Biz/Form/CheckboxToggle.vue';
    import BizFormSelect from '@/Biz/Form/Select.vue';
    import BizTag from '@/Biz/Tag.vue';
    import { filter } from 'lodash';
    import { ref } from 'vue';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: "SettingAccess",

        components: {
            BizButton,
            BizFormCheckboxToggle,
            BizFormSelect,
            BizTag,
        },

        mixins: [
            MixinHasTranslation,
        ],

        props: {
            modelValue: { type: Object, required: true },
            roleOptions: { type: Array, default: () => [] },
        },

        emits: [
            'submit'
        ],

        setup(props, {emit}) {
            return {
                form: useModelWrapper(props, emit),
                selectedUser: ref(null),
            };
        },

        computed: {
            filteredRoleOptions() {
                const self = this;

                return filter(self.roleOptions, function (option) {
                    return ! self.form.access_roles.includes(option.id)
                })
            },

            hasRoleOptions() {
                return this.filteredRoleOptions.length > 0;
            },

            hasSelectedRoles() {
                return this.form.access_roles.length > 0;
            },
        },

        methods: {
            onSelect() {
                this.form.access_roles.push(
                    this.selectedUser
                );

                this.selectedUser = null;
            },

            roleName(roleId) {
                return filter(this.roleOptions, { id: roleId })[0].value
                    ?? null;
            },

            removeSelectedRole(index) {
                this.form.access_roles.splice(index, 1);
            },
        },
    }
</script>