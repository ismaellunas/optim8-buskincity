<template>
    <div v-if="!isRecordsEmpty">
        <div class="columns">
            <div class="column is-one-third">
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
        </div>

        <biz-table-index
            :is-ajax-pagination="true"
            :query-params="queryParams"
            :records="records"
            @on-clicked-pagination="getRecords"
        >
            <template #thead>
                <tr>
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
                        <biz-button-link
                            v-if="can.edit"
                            class="is-ghost has-text-black"
                            :href="route(baseRouteName + '.edit', record.id)"
                        >
                            <span class="icon is-small">
                                <i :class="icon.eye" />
                            </span>
                        </biz-button-link>
                    </div>
                </template>
            </user-list-item>
        </biz-table-index>
    </div>
</template>

<script>
    import MixinFilterDataHandle from '@/Mixins/FilterDataHandle';
    import BizButtonLink from '@/Biz/ButtonLink.vue';
    import BizCheckbox from '@/Biz/Checkbox.vue';
    import BizDropdown from '@/Biz/Dropdown.vue';
    import BizDropdownItem from '@/Biz/DropdownItem.vue';
    import BizFilterSearch from '@/Biz/Filter/Search.vue';
    import BizIcon from '@/Biz/Icon.vue';
    import BizTableIndex from '@/Biz/TableIndex.vue';
    import UserListItem from '@/Pages/User/ListItem.vue';
    import icon from '@/Libs/icon-class';
    import { oops as oopsAlert } from '@/Libs/alert';
    import { isEmpty } from 'lodash';

    export default {
        name: 'UserListDeleted',

        components: {
            BizButtonLink,
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

        inject: {
            baseRouteName: {},
            can: {},
            roleOptions: {},
            i18n: { default: () => ({
                search : 'Search',
                filter : 'Filter',
                filter_by_role : 'Filter by role',
                name : 'Name',
                email : 'Email',
                role : 'Role',
                actions : 'Actions',
            }) },
        },

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