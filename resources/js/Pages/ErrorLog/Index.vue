<template>
    <div>
        <div class="box">
            <div class="columns">
                <div class="column is-4">
                    <biz-filter-search
                        v-model="term"
                        @search="search"
                    />
                </div>

                <div class="column is-4">
                    <biz-filter-date-range
                        v-model="dates"
                        @update:model-value="onDateRangeChanged"
                    />
                </div>

                <div class="column is-4 has-text-right">
                    <biz-button-icon
                        v-if="canDeleteCheckedRecords"
                        class="is-danger"
                        type="button"
                        :icon="icon.remove"
                        @click.prevent="deleteCheckedRecords()"
                    >
                        <span>Delete Checked Records</span>
                    </biz-button-icon>

                    <biz-button-icon
                        v-if="can.delete"
                        class="is-danger ml-2"
                        type="button"
                        :icon="icon.remove"
                        :disabled="!hasRecords"
                        @click.prevent="deleteAllRecords()"
                    >
                        <span>Delete All</span>
                    </biz-button-icon>
                </div>
            </div>

            <div class="table-container">
                <biz-table-info :records="records" />

                <biz-table class="is-striped is-fullwidth">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Created At</th>
                            <th>URL</th>
                            <th>Total Hit</th>
                            <th width="30%">Message</th>
                            <th width="12%">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr
                            v-for="(record, index) in records.data"
                            :key="index"
                        >
                            <td>
                                <biz-checkbox
                                    v-model:checked="checkedRecords"
                                    :value="record.id"
                                />
                            </td>
                            <td>{{ record.createdAtFormatted }}</td>
                            <td>{{ record.url }}</td>
                            <td>{{ record.total_hit }}</td>
                            <td style="word-break: break-word">
                                {{ record.message }}
                            </td>
                            <td>
                                <biz-button-icon
                                    v-if="can.read"
                                    class="is-ghost has-text-black ml-1"
                                    icon-class="is-small"
                                    title="View"
                                    type="button"
                                    :icon="icon.eye"
                                    @click.prevent="viewDetail(record)"
                                />

                                <biz-button-icon
                                    v-if="can.delete"
                                    class="is-ghost has-text-black ml-1"
                                    icon-class="is-small"
                                    title="Delete"
                                    type="button"
                                    :icon="icon.remove"
                                    @click.prevent="deleteRecord(record)"
                                />
                            </td>
                        </tr>
                    </tbody>
                </biz-table>
            </div>

            <biz-pagination
                :links="records.links"
                :query-params="queryParams"
            />
        </div>

        <modal-entry-detail
            v-if="isModalOpen"
            :entry="selectedEntry"
            @close-modal="closeModal"
            @open-modal="openModal"
        />
    </div>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import MixinFilterDataHandle from '@/Mixins/FilterDataHandle';
    import MixinHasModal from '@/Mixins/HasModal';
    import ModalEntryDetail from './ModalEntryDetail';
    import BizCheckbox from '@/Biz/Checkbox';
    import BizButtonIcon from '@/Biz/ButtonIcon';
    import BizFilterDateRange from '@/Biz/Filter/DateRange';
    import BizFilterSearch from '@/Biz/Filter/Search';
    import BizPagination from '@/Biz/Pagination';
    import BizTable from '@/Biz/Table';
    import BizTableInfo from '@/Biz/TableInfo';
    import icon from '@/Libs/icon-class';
    import { confirmDelete } from '@/Libs/alert';
    import { merge, isArray } from 'lodash';
    import { ref } from 'vue';

    export default {
        name: 'ErrorLog',

        components: {
            ModalEntryDetail,
            BizCheckbox,
            BizButtonIcon,
            BizFilterDateRange,
            BizFilterSearch,
            BizPagination,
            BizTable,
            BizTableInfo,
        },

        mixins: [
            MixinFilterDataHandle,
            MixinHasModal,
        ],

        layout: AppLayout,

        props: {
            baseRouteName: { type: String, required: true },
            can: { type: Object, required: true },
            pageQueryParams: { type: Object, default: () => {} },
            records: { type: Object, required: true },
        },

        setup(props) {
            const queryParams = merge(
                {},
                props.pageQueryParams
            );

            return {
                queryParams: ref(queryParams),
                term: ref(props.pageQueryParams?.term ?? null),
                dates: ref(isArray(props.pageQueryParams?.dates)
                    ? props.pageQueryParams?.dates.filter(Boolean)
                    : []
                ),
            };
        },

        data() {
            return {
                icon,
                selectedEntry: null,
                checkedRecords: [],
            };
        },

        computed: {
            hasRecords() {
                return this.records.data.length > 0;
            },

            canDeleteCheckedRecords() {
                return this.checkedRecords.length > 0
                    && this.can.delete;
            },
        },

        methods: {
            viewDetail(entry) {
                this.selectedEntry = entry;
                this.openModal();
            },

            onDateRangeChanged() {
                this.queryParams['dates'] = this.dates;
                this.refreshWithQueryParams(); // on mixin MixinFilterDataHandle
            },

            deleteRecord(record) {
                const self = this;

                confirmDelete().then((result) => {
                    if (result.isConfirmed) {
                        self.$inertia.delete(
                            route(self.baseRouteName+'.destroy', record.id),
                            {
                                onStart: () => self.onStartLoadingOverlay(),
                                onFinish: () => self.onEndLoadingOverlay(),
                            }
                        );
                    }
                });
            },

            deleteAllRecords() {
                const self = this;

                confirmDelete(
                    'Are you sure?',
                    "All records will be deleted, You won't be able to revert this!",
                ).then((result) => {
                    if (result.isConfirmed) {
                        self.$inertia.delete(
                            route(self.baseRouteName+'.destroy.all'),
                            {
                                onStart: () => self.onStartLoadingOverlay(),
                                onFinish: () => self.onEndLoadingOverlay(),
                            }
                        );
                    }
                });
            },

            deleteCheckedRecords() {
                const self = this;

                confirmDelete().then((result) => {
                    if (result.isConfirmed) {
                        self.$inertia.post(
                            route(self.baseRouteName+'.destroy.checked'),
                            {
                                recordIds: self.checkedRecords,
                            },
                            {
                                onStart: () => self.onStartLoadingOverlay(),
                                onFinish: () => {
                                    self.onEndLoadingOverlay();

                                    self.checkedRecords = [];
                                },
                            }
                        );
                    }
                });
            },

            onFinishRefreshWithQueryParams() {
                this.checkedRecords = [];
            },
        },
    };
</script>
