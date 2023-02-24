<template>
    <div v-if="!isRecordsEmpty">
        <div class="columns">
            <div class="column is-one-third">
                <biz-filter-search
                    v-model="term"
                    @search="search"
                />
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
                        <biz-icon :icon="icon.angleDown" />
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
        </div>

        <biz-table-index
            :is-ajax-pagination="true"
            :query-params="queryParams"
            :records="records"
            @on-clicked-pagination="getRecords"
        >
            <template #thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                </tr>
            </template>

            <user-list-item
                v-for="record in records.data"
                :key="record.id"
                :user="record"
            />
        </biz-table-index>
    </div>
</template>

<script>
    import MixinFilterDataHandle from '@/Mixins/FilterDataHandle';
    import BizCheckbox from '@/Biz/Checkbox';
    import BizDropdown from '@/Biz/Dropdown';
    import BizDropdownItem from '@/Biz/DropdownItem';
    import BizFilterSearch from '@/Biz/Filter/Search';
    import BizIcon from '@/Biz/Icon';
    import BizTableIndex from '@/Biz/TableIndex';
    import icon from '@/Libs/icon-class';
    import UserListItem from '@/Pages/User/ListItem';
    import { oops as oopsAlert } from '@/Libs/alert';
    import { isEmpty } from 'lodash';

    export default {
        name: 'UserListDeleted',

        components: {
            BizCheckbox,
            BizDropdown,
            BizDropdownItem,
            BizFilterSearch,
            BizIcon,
            BizTableIndex,
            UserListItem,
        },

        mixins: [
            MixinFilterDataHandle,
        ],

        inject: [
            'baseRouteName',
            'roleOptions',
        ],

        data() {
            return {
                icon,
                queryParams: {},
                records: {},
                roles: [],
                term: null,
            };
        },

        computed: {
            isRecordsEmpty() {
                return isEmpty(this.records);
            },
        },

        mounted() {
            this.getRecords();
        },

        methods: {
            getRecords(url = null) {
                const self = this;

                if (!url) {
                    url = route(self.baseRouteName + '.trashed-records');
                }

                self.onStartLoadingOverlay();

                axios.get(url, {
                    params: self.queryParams
                })
                    .then((response) => {
                        self.records = response.data;
                    })
                    .catch((errors) => {
                        oopsAlert();
                    })
                    .then(() => {
                        self.onEndLoadingOverlay();
                    })
            },

            // on MixinFilterDataHandle
            search(term) {
                this.queryParams['term'] = term;
                this.getRecords();
            },

            onRoleChanged() {
                this.queryParams['roles'] = this.roles;
                this.getRecords();
            },
        },
    }
</script>