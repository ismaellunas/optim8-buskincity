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
                        <th>Customer Name</th>
                        <th>Date</th>
                        <th>
                            <div class="level-right">
                                Actions
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="record in records.data"
                        :key="record.id"
                    >
                        <td>{{ record.status }}</td>
                        <td>{{ record.product_name }}</td>
                        <td>{{ record.customer_name ?? '-' }}</td>
                        <td>{{ record.date }}</td>
                        <td>
                            <div class="level-right">
                                <div class="buttons">
                                    <biz-button-link
                                        v-if="can.read"
                                        class="has-text-black"
                                        title="Detail"
                                        :href="route(baseRouteName+'.show', record.id)"
                                    >
                                        <biz-icon
                                            class="is-small"
                                            :icon="icon.show"
                                        />
                                    </biz-button-link>

                                    <biz-button-link
                                        v-if="record.can.reschedule"
                                        class="is-ghost is-warning"
                                        title="Reschedule"
                                        :href="route(baseRouteName + '.reschedule', record.id)"
                                    >
                                        <biz-icon
                                            class="is-small"
                                            :icon="icon.recycle"
                                        />
                                    </biz-button-link>

                                    <biz-button-icon
                                        v-if="record.can.cancel"
                                        class="is-ghost is-danger"
                                        icon-class="is-small"
                                        title="Cancel"
                                        type="button"
                                        :icon="icon.remove"
                                        @click="openModal(record)"
                                    />
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </biz-table>
        </div>

        <biz-pagination
            :links="records.links"
            :query-params="queryParams"
        />

        <modal-cancel-event-confirmation
            v-if="isModalOpen && selectedOrder"
            v-model="form.message"
            title="Cancel Event"
            :event="selectedOrder.event"
            :product-name="selectedOrder.product_name"
            @close="closeModal()"
        >
            <template #actions>
                <biz-button
                    class="is-danger ml-1"
                    type="button"
                    @click="cancel()"
                >
                    Yes for sure
                </biz-button>
            </template>
        </modal-cancel-event-confirmation>
    </div>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import BizButton from '@/Biz/Button';
    import BizButtonIcon from '@/Biz/ButtonIcon';
    import BizButtonLink from '@/Biz/ButtonLink';
    import BizCheckbox from '@/Biz/Checkbox';
    import BizDropdown from '@/Biz/Dropdown';
    import BizDropdownItem from '@/Biz/DropdownItem';
    import BizFilterSearch from '@/Biz/Filter/Search';
    import BizIcon from '@/Biz/Icon';
    import BizPagination from '@/Biz/Pagination';
    import BizTable from '@/Biz/Table';
    import MixinFilterDataHandle from '@/Mixins/FilterDataHandle';
    import MixinHasModal from '@/Mixins/HasModal';
    import ModalCancelEventConfirmation from '@booking/Pages/ModalCancelEventConfirmation';
    import icon from '@/Libs/icon-class';
    import { confirmDelete, oops as oopsAlert, success as successAlert } from '@/Libs/alert';
    import { merge } from 'lodash';
    import { ref } from "vue";
    import { useForm } from '@inertiajs/inertia-vue3';

    export default {
        components: {
            BizButton,
            BizButtonIcon,
            BizButtonLink,
            BizCheckbox,
            BizDropdown,
            BizDropdownItem,
            BizFilterSearch,
            BizIcon,
            BizPagination,
            BizTable,
            ModalCancelEventConfirmation,
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
            statusOptions: { type: Object, required: true },
        },

        setup(props) {
            const queryParams = merge(
                {},
                props.pageQueryParams
            );

            const form = {
                message: null,
            };

            return {
                queryParams: ref(queryParams),
                statuses: ref(props.pageQueryParams?.status ?? []),
                term: ref(props.pageQueryParams?.term ?? null),
                form: useForm(form),
                selectedOrder: ref(null),
                icon,
            };
        },

        methods: {
            deleteRecord(record) {
                const self = this;

                confirmDelete().then(result => {
                    if (result.isConfirmed) {
                        self.$inertia.delete(
                            route(this.baseRouteName+'.destroy', record.id),
                            {
                                onStart: self.onStartLoadingOverlay,
                                onFinish: self.onEndLoadingOverlay,
                                onError: () => {
                                    oopsAlert();
                                },
                                onSuccess: (page) => {
                                    successAlert(page.props.flash.message);
                                },
                            }
                        );
                    }
                })
            },

            onStatusChanged() {
                this.queryParams['status'] = this.statuses;
                this.refreshWithQueryParams(); // on mixin MixinFilterDataHandle
            },

            cancel() {
                const self = this;

                self.form.post(
                    route(self.baseRouteName + '.cancel', self.selectedOrder.id),
                    {
                        onStart: () => self.onStartLoadingOverlay(),
                        onFinish: () => self.onEndLoadingOverlay(),
                        onError: (errors) => {
                            oopsAlert();
                        },
                        onSuccess: (page) => {
                            self.closeModal();

                            successAlert(page.props.flash.message);
                        },
                    }
                );
            },

            openModal(record) { /* @see MixinHasModal */
                this.selectedOrder = record;
                this.form.reset();

                this.isModalOpen = true;
                this.onShownModal();
            },
        },
    };
</script>
