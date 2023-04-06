<template>
    <div>
        <div class="box">
            <div class="columns">
                <div class="column is-4">
                    <biz-filter-search
                        v-model="term"
                        :placeholder="i18n.search"
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
                        <span>{{ i18n.delete_checked_records }}</span>
                    </biz-button-icon>

                    <biz-button-icon
                        v-if="can.delete"
                        class="is-danger ml-2"
                        type="button"
                        :icon="icon.remove"
                        :disabled="!hasRecords"
                        @click.prevent="deleteAllRecords()"
                    >
                        <span>{{ i18n.delete_all }}</span>
                    </biz-button-icon>
                </div>
            </div>

            <biz-table-index
                :records="records"
                :query-params="queryParams"
            >
                <template #thead>
                    <tr>
                        <th>#</th>
                        <th>{{ i18n.created_at }}</th>
                        <th>{{ i18n.url }}</th>
                        <th>{{ i18n.total_hit }}</th>
                        <th width="30%">
                            {{ i18n.message }}
                        </th>
                        <th width="12%">
                            {{ i18n.actions }}
                        </th>
                    </tr>
                </template>

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
                    <td>{{ limitString(record.url) }}</td>
                    <td>{{ record.total_hit }}</td>
                    <td style="word-break: break-word">
                        {{ limitString(record.message) }}
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
            </biz-table-index>
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
    import MixinFilterDataHandle from '@/Mixins/FilterDataHandle';
    import MixinHasModal from '@/Mixins/HasModal';
    import AppLayout from '@/Layouts/AppLayout.vue';
    import ModalEntryDetail from './ModalEntryDetail.vue';
    import BizCheckbox from '@/Biz/Checkbox.vue';
    import BizButtonIcon from '@/Biz/ButtonIcon.vue';
    import BizFilterDateRange from '@/Biz/Filter/DateRange.vue';
    import BizFilterSearch from '@/Biz/Filter/Search.vue';
    import BizTableIndex from '@/Biz/TableIndex.vue';
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
            BizTableIndex,
        },

        mixins: [
            MixinFilterDataHandle,
            MixinHasModal,
        ],

        provide() {
            return {
                i18n: this.i18n,
            };
        },

        layout: AppLayout,

        props: {
            baseRouteName: { type: String, required: true },
            can: { type: Object, required: true },
            pageQueryParams: { type: Object, default: () => {} },
            records: { type: Object, required: true },
            i18n: { type: Object, default: () => ({
                search : 'Search',
                delete_all : 'Delete All',
                delete_checked_records : 'Delete Checked Records',
                created_at : 'Created At',
                url : 'URL',
                total_hit : 'Total Hit',
                message : 'Message',
                actions : 'Actions',
            }) },
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

            limitString(text, limit = 100) {
                if (text.length > 100) {
                    return text.substring(0, limit) + '...';
                }

                return text;
            }
        },
    };
</script>
