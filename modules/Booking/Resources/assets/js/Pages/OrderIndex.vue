<template>
    <div class="box">
        <div class="columns is-multiline">
            <div class="column is-4-desktop">
                <biz-filter-search
                    v-model="term"
                    @search="search"
                />
            </div>

            <div class="column is-2-desktop">
                <biz-select
                    v-model="status"
                    @change="onStatusChanged()"
                >
                    <option
                        v-for="statusOption in statusOptions"
                        :key="statusOption.id"
                        :value="statusOption.id"
                    >
                        <span>{{ statusOption.value }}</span>
                    </option>
                </biz-select>
            </div>

            <div class="column is-4 is-3-widescreen">
                <biz-filter-date-range
                    v-model="dates"
                    max-range="31"
                    @update:model-value="onDateRangeChanged"
                />
            </div>

            <div class="column is-narrow is-3-widescreen">
                <biz-dropdown-search
                    class="is-fullwidth"
                    :close-on-click="true"
                    @search="searchCity($event)"
                >
                    <template #trigger>
                        <span>
                            {{ queryParams.city ?? 'Any' }}
                        </span>
                    </template>

                    <biz-dropdown-item
                        @click="onCityChange()"
                    >
                        Any
                    </biz-dropdown-item>

                    <biz-dropdown-item
                        v-for="(option, index) in filteredCities"
                        :key="index"
                        @click="onCityChange(option)"
                    >
                        {{ option }}
                    </biz-dropdown-item>
                </biz-dropdown-search>
            </div>
        </div>

        <biz-table-index
            :records="records"
            :query-params="queryParams"
        >
            <template #thead>
                <tr>
                    <th>Status</th>
                    <th>Name</th>
                    <th>City</th>
                    <th>User</th>
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
            </template>

            <tr
                v-for="(record, index) in records.data"
                :key="record.id"
            >
                <td>{{ record.status }}</td>
                <td>{{ record.product_name }}</td>
                <td>{{ record.city }}</td>
                <td>{{ record.customer_name ?? '-' }}</td>
                <td>{{ record.date }}</td>
                <td>{{ record.timezone }}</td>
                <td>{{ record.start_end_time }}</td>
                <td>{{ record.check_in_time }}</td>
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

                            <biz-dropdown
                                v-if="hasMoreAction(record)"
                                class="is-right"
                                :class="{'is-up': index > records.data.length - 3}"
                                :close-on-click="false"
                            >
                                <template #trigger>
                                    <biz-icon
                                        class="is-small"
                                        :icon="icon.ellipsis"
                                    />
                                </template>

                                <biz-link
                                    v-if="record.can.reschedule"
                                    class="dropdown-item"
                                    :href="route(baseRouteName + '.reschedule', record.id)"
                                >
                                    <biz-icon
                                        class="is-small"
                                        :icon="icon.recycle"
                                    />
                                    &nbsp;<span>Reschedule</span>
                                </biz-link>

                                <biz-dropdown-item
                                    v-if="record.can.cancel"
                                    tag="a"
                                    @click.prevent="openModal(record)"
                                >
                                    <biz-icon
                                        class="is-small"
                                        :icon="icon.remove"
                                    />
                                    &nbsp;<span>Cancel</span>
                                </biz-dropdown-item>
                            </biz-dropdown>
                        </div>
                    </div>
                </td>
            </tr>
        </biz-table-index>

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
    import MixinFilterDataHandle from '@/Mixins/FilterDataHandle';
    import MixinHasModal from '@/Mixins/HasModal';
    import AppLayout from '@/Layouts/AppLayout.vue';
    import BizButton from '@/Biz/Button.vue';
    import BizButtonLink from '@/Biz/ButtonLink.vue';
    import BizDropdown from '@/Biz/Dropdown.vue';
    import BizDropdownItem from '@/Biz/DropdownItem.vue';
    import BizDropdownSearch from '@/Biz/DropdownSearch.vue';
    import BizFilterDateRange from '@/Biz/Filter/DateRange.vue';
    import BizFilterSearch from '@/Biz/Filter/Search.vue';
    import BizIcon from '@/Biz/Icon.vue';
    import BizLink from '@/Biz/Link.vue';
    import BizTableIndex from '@/Biz/TableIndex.vue';
    import BizSelect from '@/Biz/Select.vue';
    import ModalCancelEventConfirmation from '@booking/Pages/ModalCancelEventConfirmation.vue';
    import icon from '@/Libs/icon-class';
    import { confirmDelete, oops as oopsAlert, success as successAlert } from '@/Libs/alert';
    import { debounce, isEmpty, isArray, filter, merge } from 'lodash';
    import { debounceTime } from '@/Libs/defaults';
    import { ref } from "vue";
    import { useForm } from '@inertiajs/vue3';

    export default {
        components: {
            BizButton,
            BizButtonLink,
            BizDropdown,
            BizDropdownItem,
            BizDropdownSearch,
            BizFilterDateRange,
            BizFilterSearch,
            BizIcon,
            BizLink,
            BizSelect,
            BizTableIndex,
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
            cityOptions: { type: Object, required: true },
        },

        setup(props) {
            const form = {
                message: null,
            };

            return {
                queryParams: ref({ ...{}, ...props.pageQueryParams }),
                dates: ref(isArray(props.pageQueryParams?.dates)
                    ? props.pageQueryParams?.dates.filter(Boolean)
                    : []
                ),
                status: ref(props.pageQueryParams?.status ?? null),
                term: ref(props.pageQueryParams?.term ?? null),
                cityTerm: ref(null),
                form: useForm(form),
                selectedOrder: ref(null),
                icon,
                filteredCities: ref(props.cityOptions.slice(0, 10)),
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
                this.queryParams['status'] = this.status;
                this.refreshWithQueryParams(); // on mixin MixinFilterDataHandle
            },

            onDateRangeChanged() {
                this.queryParams['dates'] = this.dates;
                this.refreshWithQueryParams(); // on mixin MixinFilterDataHandle
            },

            onCityChange(city = null) {
                this.queryParams['city'] = city;
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

            hasMoreAction(record) {
                return (record.can.reschedule || record.can.cancel);
            },

            filterCities() {
                const self = this;
                if (!isEmpty(this.cityTerm) && this.cityTerm.length > 1) {
                    return filter(this.cityOptions, function (city) {
                        return new RegExp(self.cityTerm, 'i').test(city);
                    }).slice(0, 10);
                } else {
                    return this.cityOptions.slice(0, 10);
                }
            },

            searchCity: debounce(function(term) {
                this.cityTerm = term;
                this.filteredCities = this.filterCities();
            }, debounceTime),
        },
    };
</script>
