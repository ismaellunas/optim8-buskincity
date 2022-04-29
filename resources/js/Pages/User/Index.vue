<template>
    <app-layout>
        <template #header>
            {{ title }}
        </template>

        <div class="box">
            <div class="columns">
                <div class="column">
                    <div class="is-pulled-left">
                        <biz-filter-search
                            v-model="term"
                            @search="search"
                        />
                    </div>
                </div>

                <div class="column">
                    <biz-dropdown
                        :close-on-click="false"
                    >
                        <template #trigger>
                            <span>Filter</span>
                            <span
                                v-if="roles.length > 0"
                                class="ml-1"
                            >
                                ({{ roles.length }})
                            </span>
                            <span class="icon is-small">
                                <i
                                    class="fas fa-angle-down"
                                    aria-hidden="true"
                                />
                            </span>
                        </template>

                        <biz-dropdown-item>
                            Filter by Role
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

                <div class="column">
                    <div
                        v-if="can.add"
                        class="is-pulled-right"
                    >
                        <biz-button-link
                            class="is-primary"
                            :href="route(baseRouteName+'.create')"
                        >
                            <span class="icon is-small">
                                <i class="fas fa-plus" />
                            </span>
                            <span>Add New</span>
                        </biz-button-link>
                    </div>
                </div>
            </div>

            <div class="table-container">
                <biz-table class="is-striped is-hoverable is-fullwidth">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>
                                <div class="level-right">
                                    Actions
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
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
                                        <i class="fas fa-id-card" />
                                    </a>

                                    <biz-button-link
                                        v-if="can.edit"
                                        class="is-ghost has-text-black"
                                        :href="route(baseRouteName + '.edit', record.id)"
                                    >
                                        <span class="icon is-small">
                                            <i class="fas fa-pen" />
                                        </span>
                                    </biz-button-link>

                                    <template
                                        v-if="can.delete && record.can.delete_user"
                                    >
                                        <biz-button-icon
                                            class="is-ghost has-text-black ml-1"
                                            icon-class="is-small"
                                            icon="far fa-trash-alt"
                                            title="Delete User"
                                            @click.prevent="deleteUserModal(record)"
                                        />
                                        <biz-button-icon
                                            v-if="!record.is_suspended"
                                            class="is-ghost has-text-black ml-1"
                                            icon-class="is-small"
                                            icon="fas fa-ban"
                                            title="Suspend User"
                                            @click.prevent="suspendUser(record)"
                                        />
                                        <biz-button-icon
                                            v-if="record.is_suspended"
                                            class="is-ghost has-text-black ml-1"
                                            icon-class="is-small"
                                            icon="fas fa-hands-helping"
                                            title="Unsuspend User"
                                            @click.prevent="unsuspendUser(record)"
                                        />
                                    </template>
                                </div>
                            </template>
                        </user-list-item>
                    </tbody>
                </biz-table>
            </div>

            <biz-pagination
                :links="records.links"
                :query-params="queryParams"
            />
        </div>

        <modal-form-delete
            v-if="isModalOpen"
            :errors="errors"
            :get-candidates-route="baseApiRouteName+'.reassignment-candidates'"
            :user="selectedUser"
            @close="closeModal"
            @delete-user="deleteUser"
        />
    </app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import MixinFilterDataHandle from '@/Mixins/FilterDataHandle';
    import MixinHasModal from '@/Mixins/HasModal';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import ModalFormDelete from '@/Pages/User/ModalFormDelete';
    import BizButtonIcon from '@/Biz/ButtonIcon';
    import BizButtonLink from '@/Biz/ButtonLink';
    import BizCheckbox from '@/Biz/Checkbox';
    import BizDropdown from '@/Biz/Dropdown';
    import BizDropdownItem from '@/Biz/DropdownItem';
    import BizFilterSearch from '@/Biz/Filter/Search';
    import BizPagination from '@/Biz/Pagination';
    import BizTable from '@/Biz/Table';
    import UserListItem from '@/Pages/User/ListItem';
    import { confirmDelete, oops as oopsAlert, success as successAlert } from '@/Libs/alert';
    import { merge } from 'lodash';
    import { ref } from 'vue';

    export default {
        components: {
            AppLayout,
            ModalFormDelete,
            BizButtonIcon,
            BizButtonLink,
            BizCheckbox,
            BizDropdown,
            BizDropdownItem,
            BizFilterSearch,
            BizPagination,
            BizTable,
            UserListItem,
        },

        mixins: [
            MixinFilterDataHandle,
            MixinHasModal,
            MixinHasPageErrors,
        ],

        props: {
            baseApiRouteName: { type: String, required: true },
            baseRouteName: { type: String, required: true },
            can: { type: Object, required: true },
            errors: { type: Object, default: () => {} },
            pageNumber: { type: String, default: null },
            pageQueryParams: { type: Object, required: true },
            records: { type: Object, default: () => {} },
            roleOptions: { type: Object, required: true },
            title: { type: String, required: true },
        },

        setup(props) {
            const queryParams = merge(
                {},
                props.pageQueryParams
            );

            return {
                queryParams: ref(queryParams),
                term: ref(props.pageQueryParams?.term ?? null),
                roles: ref(props.pageQueryParams?.roles ?? []),
            };
        },

        data() {
            return {
                seletectedUser: null,
                loader: null,
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
                    'Are you sure?',
                    'Once you hit "Confirm Deletion", the user will be permanently removed.',
                    'Confirm Deletion'
                ).then(result => {
                    if (result.isConfirmed) {
                        const userId = this.selectedUser.id;

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
                    'Are you sure?',
                    'The user will be suspended.'
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
                    'Are you sure?',
                    'The user will be unsuspended.'
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
        }
    };
</script>
