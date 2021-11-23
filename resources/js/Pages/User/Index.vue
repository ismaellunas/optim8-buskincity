<template>
<app-layout>
    <template v-slot:header>{{ title }}</template>

    <div class="box">
        <div class="columns">
            <div class="column">
                <div class="is-pulled-left">
                    <sdb-filter-search
                        v-model="term"
                        @search="search"
                    ></sdb-filter-search>
                </div>
            </div>

            <div class="column">
                <sdb-dropdown
                    :close-on-click="false"
                >
                    <template v-slot:trigger>
                        <span>Filter</span>
                        <span
                            v-if="roles.length > 0"
                            class="ml-1"
                        >
                            ({{roles.length}})
                        </span>
                        <span class="icon is-small">
                            <i class="fas fa-angle-down" aria-hidden="true"></i>
                        </span>
                    </template>

                    <sdb-dropdown-item>
                        Filter by Role
                    </sdb-dropdown-item>

                    <sdb-dropdown-item v-for="role in roleOptions">
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
                            <i class="fas fa-plus"></i>
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
                        :user="record"
                    >
                        <template v-slot:actions>
                            <div class="level-right">
                                <sdb-button-link
                                    v-if="can.edit"
                                    class="is-ghost has-text-black"
                                    :href="route(baseRouteName + '.edit', record.id)"
                                >
                                    <span class="icon is-small">
                                        <i class="fas fa-pen"></i>
                                    </span>
                                </sdb-button-link>
                                <sdb-button
                                    v-if="can.delete && record.can.delete_user"
                                    class="is-ghost has-text-black ml-1"
                                    @click.prevent="deleteRecord(record)"
                                >
                                    <span class="icon is-small">
                                        <i class="far fa-trash-alt"></i>
                                    </span>
                                </sdb-button>
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
</app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import MixinFilterDataHandle from '@/Mixins/FilterDataHandle';
    import SdbButton from '@/Sdb/Button';
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
            SdbButton,
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
        ],
        props: {
            baseRouteName: String,
            can: Object,
            pageNumber: String,
            pageQueryParams: Object,
            records: {},
            roleOptions: Object,
            title: String,
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
        methods: {
            deleteRecord(record) {
                const self = this;
                confirmDelete().then(result => {
                    if (result.isConfirmed) {
                        self.$inertia.delete(
                            route(self.baseRouteName+'.destroy', record.id),
                            {
                                onStart: self.onStartLoadingOverlay,
                                onFinish: self.onEndLoadingOverlay,
                                onError: self.onError,
                                onSuccess: self.onSuccess,
                            }
                        );
                    }
                })
            },
            onError(errors) {
                oopsAlert();
            },
            onSuccess(page) {
                successAlert(page.props.flash.message);
            },
            onRoleChanged(event) {
                this.queryParams['roles'] = this.roles;
                this.refreshWithQueryParams(); // on mixin MixinFilterDataHandle
            },
        },
    };
</script>
