<template>
    <app-layout>
        <template #header>{{ title }}</template>

        <div class="box">
            <div class="columns">
                <div class="column">
                    <div class="is-pulled-left">
                        <sdb-filter-search
                            v-model="term"
                            @search="search"
                        />
                    </div>
                </div>

                <div class="column">
                    <sdb-dropdown
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

                        <sdb-dropdown-item>
                            Filter by Role
                        </sdb-dropdown-item>

                        <sdb-dropdown-item
                            v-for="role in roleOptions"
                            :key="role.id"
                        >
                            <sdb-checkbox
                                v-model:checked="roles"
                                :value="role.id"
                                @change="onRoleChanged"
                            >
                                &nbsp; {{ role.value }}
                            </sdb-checkbox>
                        </sdb-dropdown-item>
                    </sdb-dropdown>
                </div>

                <div class="column">
                    <div
                        v-if="can.add"
                        class="is-pulled-right"
                    >
                        <sdb-button-link
                            class="is-primary"
                            :href="route(baseRouteName+'.create')"
                        >
                            <span class="icon is-small">
                                <i class="fas fa-plus" />
                            </span>
                            <span>Add New</span>
                        </sdb-button-link>
                    </div>
                </div>
            </div>

            <div class="table-container">
                <sdb-table class="is-striped is-hoverable is-fullwidth">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>
                                <div class="level-right">Actions</div>
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
                                    <sdb-button-link
                                        v-if="can.edit"
                                        class="is-ghost has-text-black"
                                        :href="route(baseRouteName + '.edit', record.id)"
                                    >
                                        <span class="icon is-small">
                                            <i class="fas fa-pen" />
                                        </span>
                                    </sdb-button-link>
                                    <sdb-button-icon
                                        v-if="can.delete && record.can.delete_user"
                                        class="is-ghost has-text-black ml-1"
                                        icon-class="is-small"
                                        icon="far fa-trash-alt"
                                        @click.prevent="deleteUserModal(record)"
                                    />
                                </div>
                            </template>
                        </user-list-item>
                    </tbody>
                </sdb-table>
            </div>

            <sdb-pagination
                :links="records.links"
                :query-params="queryParams"
            />
        </div>

        <modal-form-delete
            v-if="isModalOpen"
            :errors="errors"
            :get-candidates-route="baseRouteName+'.reassignment-candidates'"
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
    import SdbButtonIcon from '@/Sdb/ButtonIcon';
    import SdbButtonLink from '@/Sdb/ButtonLink';
    import SdbCheckbox from '@/Sdb/Checkbox';
    import SdbDropdown from '@/Sdb/Dropdown';
    import SdbDropdownItem from '@/Sdb/DropdownItem';
    import SdbFilterSearch from '@/Sdb/Filter/Search';
    import SdbPagination from '@/Sdb/Pagination';
    import SdbTable from '@/Sdb/Table';
    import UserListItem from '@/Pages/User/ListItem';
    import { confirmDelete, oops as oopsAlert, success as successAlert } from '@/Libs/alert';
    import { merge } from 'lodash';
    import { ref } from 'vue';

    export default {
        components: {
            AppLayout,
            ModalFormDelete,
            SdbButtonIcon,
            SdbButtonLink,
            SdbCheckbox,
            SdbDropdown,
            SdbDropdownItem,
            SdbFilterSearch,
            SdbPagination,
            SdbTable,
            UserListItem,
        },

        mixins: [
            MixinFilterDataHandle,
            MixinHasModal,
            MixinHasPageErrors,
        ],

        props: {
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
                        const userId = form.user.id

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
            }
        }
    };
</script>
