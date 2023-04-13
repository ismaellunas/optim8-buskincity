<template>
    <div>
        <div class="columns">
            <div class="column">
                <biz-filter-search
                    v-model="term"
                    :placeholder="i18n.search"
                    @search="search"
                />
            </div>

            <div class="column">
                <biz-dropdown
                    :close-on-click="false"
                >
                    <template #trigger>
                        <span>{{ i18n.filter }}</span>
                        <span
                            v-if="roles.length > 0"
                            class="ml-1"
                        >
                            ({{ roles.length }})
                        </span>
                        <biz-icon :icon="icon.angleDown" />
                    </template>

                    <biz-dropdown-item>
                        {{ i18n.filter_by_role }}
                    </biz-dropdown-item>

                    <biz-dropdown-item
                        v-for="role in roleOptions"
                        :key="role.id"
                    >
                        <biz-checkbox
                            v-model:checked="roles"
                            :value="role.id"
                            @change="onRoleChanged"
                        >
                            &nbsp; {{ role.value }}
                        </biz-checkbox>
                    </biz-dropdown-item>
                </biz-dropdown>
            </div>

            <div class="column has-text-right">
                <biz-button-link
                    v-if="can.add"
                    class="is-primary"
                    :href="route(baseRouteName+'.create')"
                >
                    <biz-icon :icon="icon.add" />
                    <span>{{ i18n.create_new }}</span>
                </biz-button-link>
            </div>
        </div>

        <biz-table-index
            :records="records"
            :query-params="queryParams"
        >
            <template #thead>
                <tr>
                    <th>#</th>
                    <th>{{ i18n.name }}</th>
                    <th>{{ i18n.email }}</th>
                    <th>{{ i18n.role }}</th>
                    <th>
                        <div class="level-right">
                            {{ i18n.actions }}
                        </div>
                    </th>
                </tr>
            </template>

            <user-list-item
                v-for="record in records.data"
                :key="record.id"
                :user="record"
            >
                <template #actions>
                    <div class="level-right">
                        <a
                            v-if="record.can.public_profile"
                            class="button is-ghost has-text-black ml-1"
                            target="_blank"
                            title="Profile Page Url"
                            :href="record.profile_page_url"
                        >
                            <i :class="icon.idCard" />
                        </a>

                        <biz-button-link
                            v-if="can.edit"
                            class="is-ghost has-text-black"
                            :href="route(baseRouteName + '.edit', record.id)"
                        >
                            <span class="icon is-small">
                                <i :class="icon.edit" />
                            </span>
                        </biz-button-link>

                        <template
                            v-if="can.delete && record.can.delete_user"
                        >
                            <biz-button-icon
                                class="is-ghost has-text-black ml-1"
                                icon-class="is-small"
                                :icon="icon.remove"
                                title="Delete User"
                                @click.prevent="deleteUserModal(record)"
                            />
                            <biz-button-icon
                                v-if="!record.is_suspended"
                                class="is-ghost has-text-black ml-1"
                                icon-class="is-small"
                                :icon="icon.suspend"
                                title="Suspend User"
                                @click.prevent="suspendUser(record)"
                            />
                            <biz-button-icon
                                v-if="record.is_suspended"
                                class="is-ghost has-text-black ml-1"
                                icon-class="is-small"
                                :icon="icon.unsuspend"
                                title="Unsuspend User"
                                @click.prevent="unsuspendUser(record)"
                            />
                        </template>
                    </div>
                </template>
            </user-list-item>
        </biz-table-index>



        <modal-form-delete
            v-if="isModalOpen"
            :get-candidates-route="baseRouteName+'.reassignment-candidates'"
            :user="selectedUser"
            @close="closeModal"
            @delete-user="deleteUser"
        />
    </div>
</template>

<script>
    import MixinFilterDataHandle from '@/Mixins/FilterDataHandle';
    import MixinHasModal from '@/Mixins/HasModal';
    import BizButtonIcon from '@/Biz/ButtonIcon.vue';
    import BizButtonLink from '@/Biz/ButtonLink.vue';
    import BizCheckbox from '@/Biz/Checkbox.vue';
    import BizDropdown from '@/Biz/Dropdown.vue';
    import BizDropdownItem from '@/Biz/DropdownItem.vue';
    import BizFilterSearch from '@/Biz/Filter/Search.vue';
    import BizIcon from '@/Biz/Icon.vue';
    import BizTableIndex from '@/Biz/TableIndex.vue';
    import ModalFormDelete from '@/Pages/User/ModalFormDelete.vue';
    import UserListItem from '@/Pages/User/ListItem.vue';
    import icon from '@/Libs/icon-class';
    import { confirmDelete, oops as oopsAlert, success as successAlert } from '@/Libs/alert';
    import { merge } from 'lodash';
    import { ref } from 'vue';

    export default {
        name: 'UserList',

        components: {
            BizButtonIcon,
            BizButtonLink,
            BizCheckbox,
            BizDropdown,
            BizDropdownItem,
            BizFilterSearch,
            BizIcon,
            BizTableIndex,
            ModalFormDelete,
            UserListItem,
        },

        mixins: [
            MixinFilterDataHandle,
            MixinHasModal,
        ],

        inject: {
            baseRouteName: {},
            can: {},
            roleOptions: {},
            i18n: { default: () => ({
                search : 'Search',
                filter : 'Filter',
                filter_by_role : 'Filter by role',
                create_new : 'Create new',
                name : 'Name',
                email : 'Email',
                role : 'Role',
                actions : 'Actions',
                are_you_sure : 'Are you sure?',
                delete_confirmation : 'Once you hit "Confirm Deletion", the user will be permanently removed.',
                confirm_deletion : 'Confirm deletion',
                suspend_user_confirmation : 'The user will be suspended.',
                unsuspend_user_confirmation : 'The user will be unsuspended.',
            }) },
        },

        props: {
            pageQueryParams: { type: Object, required: true },
            records: { type: Object, default: () => {} },
        },

        setup(props) {
            const queryParams = merge(
                {},
                props.pageQueryParams
            );

            return {
                queryParams: ref(queryParams),
                roles: ref(props.pageQueryParams?.roles ?? []),
                term: ref(props.pageQueryParams?.term ?? null),
            };
        },

        data() {
            return {
                icon,
                seletectedUser: null,
            };
        },

        methods: {
            deleteUserModal(user) {
                this.selectedUser = user;
                this.openModal();
            },

            onError(errors) {
                oopsAlert();
            },

            onSuccess(page) {
                successAlert(page.props.flash.message);
                this.closeModal();
            },

            onRoleChanged(event) {
                this.queryParams['roles'] = this.roles;
                this.refreshWithQueryParams(); // on mixin MixinFilterDataHandle
            },

            deleteUser(form) {
                const self = this;

                confirmDelete(
                    self.i18n.are_you_sure,
                    self.i18n.delete_confirmation,
                    self.i18n.confirm_deletion,
                ).then(result => {
                    if (result.isConfirmed) {
                        const userId = self.selectedUser.id;

                        form.delete(
                            route(self.baseRouteName+'.destroy', userId),
                            {
                                onStart: self.onStartLoadingOverlay,
                                onFinish: () => {
                                    self.onEndLoadingOverlay();
                                },
                                onError: self.onError,
                                onSuccess: self.onSuccess,
                            }
                        );
                    }
                })
            },

            suspendUser(user) {
                const self = this;

                confirmDelete(
                    self.i18n.are_you_sure,
                    self.i18n.suspend_user_confirmation,
                ).then(result => {
                    if (result.isConfirmed) {

                        self.$inertia.post(
                            route(self.baseRouteName+'.suspend', user.id),
                            {},
                            {
                                onStart: self.onStartLoadingOverlay,
                                onFinish: self.onEndLoadingOverlay,
                                onError: self.onError,
                                onSuccess: self.onSuccess,
                            }
                        );
                    }
                });
            },

            unsuspendUser(user) {
                const self = this;

                confirmDelete(
                    self.i18n.are_you_sure,
                    self.i18n.unsuspend_user_confirmation,
                ).then(result => {
                    if (result.isConfirmed) {

                        self.$inertia.post(
                            route(self.baseRouteName+'.unsuspend', user.id),
                            {},
                            {
                                onStart: self.onStartLoadingOverlay,
                                onFinish: self.onEndLoadingOverlay,
                                onError: self.onError,
                                onSuccess: self.onSuccess,
                            }
                        );
                    }
                });
            },
        },
    }
</script>