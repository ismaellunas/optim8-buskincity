<template>
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
                <biz-dropdown :close-on-click="false">
                    <template #trigger>
                        <span>Filter</span>

                        <span class="icon is-small">
                            <i
                                :class="icon.angleDown"
                                aria-hidden="true"
                            />
                        </span>
                    </template>

                    <biz-dropdown-item>
                        Status
                    </biz-dropdown-item>

                    <biz-dropdown-item
                        v-for="status in statusOptions"
                        :key="status.id"
                    >
                        <biz-checkbox
                            v-model:checked="statuses"
                            :value="status.id"
                            @change="onStatusChanged"
                        >
                            &nbsp; {{ status.value }}
                        </biz-checkbox>
                    </biz-dropdown-item>
                </biz-dropdown>
            </div>

            <div class="column">
                &nbsp;
            </div>
        </div>

        <div class="table-container">
            <biz-table class="is-striped is-hoverable is-fullwidth">
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Name</th>
                        <th>Date</th>
                        <th>Timezone</th>
                        <th>Time</th>
                        <th>Check-In</th>
                        <th>
                            <div class="level-right">
                                Actions
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="order in orders.data"
                        :key="order.id"
                    >
                        <td>
                            <biz-tag class="is-medium">
                                {{ order.status }}
                            </biz-tag>
                        </td>
                        <td>{{ order.product_name }}</td>
                        <td>{{ order.date }}</td>
                        <td>{{ order.timezone }}</td>
                        <td>{{ order.start_end_time }}</td>
                        <td>{{ order.check_in_time }}</td>
                        <td>
                            <div class="level-right">
                                <biz-button-link
                                    class="is-ghost has-text-black"
                                    :href="route(baseRouteName+'.show', order.id)"
                                >
                                    <biz-icon
                                        class="is-small"
                                        :icon="icon.show"
                                    />
                                </biz-button-link>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </biz-table>
        </div>

        <biz-pagination
            :links="orders.links"
            :query-params="queryParams"
        />
    </div>
</template>

<script>
    import Layout from '@/Layouts/User';
    import BizButtonIcon from '@/Biz/ButtonIcon';
    import BizButtonLink from '@/Biz/ButtonLink';
    import BizCheckbox from '@/Biz/Checkbox';
    import BizDropdown from '@/Biz/Dropdown';
    import BizDropdownItem from '@/Biz/DropdownItem';
    import BizFilterSearch from '@/Biz/Filter/Search';
    import BizIcon from '@/Biz/Icon';
    import BizPagination from '@/Biz/Pagination';
    import BizTable from '@/Biz/Table';
    import BizTag from '@/Biz/Tag';
    import MixinFilterDataHandle from '@/Mixins/FilterDataHandle';
    import icon from '@/Libs/icon-class';
    import { confirmDelete, oops as oopsAlert, success as successAlert } from '@/Libs/alert';
    import { merge } from 'lodash';
    import { ref } from "vue";

    export default {
        components: {
            BizButtonLink,
            BizCheckbox,
            BizDropdown,
            BizDropdownItem,
            BizFilterSearch,
            BizIcon,
            BizPagination,
            BizTable,
            BizTag,
        },

        mixins: [
            MixinFilterDataHandle,
        ],

        layout: Layout,

        props: {
            baseRouteName: { type: String, required: true },
            pageQueryParams: { type: Object, default: () => {} },
            orders: { type: Object, required: true },
            statusOptions: { type: Object, required: true },
        },

        setup(props) {
            const queryParams = merge(
                {},
                props.pageQueryParams
            );

            return {
                icon,
                queryParams: ref(queryParams),
                term: ref(props.pageQueryParams?.term ?? null),
                statuses: ref(props.pageQueryParams?.status ?? []),
            };
        },

        methods: {
            onStatusChanged() {
                this.queryParams['status'] = this.statuses;
                this.refreshWithQueryParams(); // on mixin MixinFilterDataHandle
            },
        },
    };
</script>
