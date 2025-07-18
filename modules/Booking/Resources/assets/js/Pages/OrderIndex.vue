<template>
    <div class="box">
        <div class="columns is-multiline">
            <div class="column is-4-desktop">
                <biz-filter-search
                    v-model="term"
                    :placeholder="i18n.search"
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
                <biz-select
                    v-model="location"
                    :placeholder="i18n.any"
                    @change="onLocationChanged()"
                >
                    <option
                        v-for="locationOption in computedLocationOptions"
                        :key="locationOption.id"
                        :value="locationOption.id"
                    >
                        {{ locationOption.value }}
                    </option>
                </biz-select>
            </div>
        </div>

        <biz-table-index
            :records="records"
            :query-params="queryParams"
        >
            <template #thead>
                <tr>
                    <th>{{ i18n.status }}</th>
                    <th>{{ i18n.name }}</th>
                    <th>{{ i18n.location }}</th>
                    <th>{{ i18n.user }}</th>
                    <th>{{ i18n.date }}</th>
                    <th>{{ i18n.timezone }}</th>
                    <th>{{ i18n.time }}</th>
                    <th>{{ i18n.check_in }}</th>
                    <th>
                        <div class="level-right">
                            {{ i18n.actions }}
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
                <td>{{ record.location ?? '-' }}</td>
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
                                    &nbsp;<span>{{ i18n.reschedule }}</span>
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
                                    &nbsp;<span>{{ i18n.cancel }}</span>
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
            :title="i18n.cancel_event"
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
                    {{ i18n.yes }}
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
    import BizFilterDateRange from '@/Biz/Filter/DateRange.vue';
    import BizFilterSearch from '@/Biz/Filter/Search.vue';
    import BizIcon from '@/Biz/Icon.vue';
    import BizLink from '@/Biz/Link.vue';
    import BizTableIndex from '@/Biz/TableIndex.vue';
    import BizSelect from '@/Biz/Select.vue';
    import ModalCancelEventConfirmation from '@booking/Pages/ModalCancelEventConfirmation.vue';
    import icon from '@/Libs/icon-class';
    import { confirmDelete, oops as oopsAlert, success as successAlert } from '@/Libs/alert';
    import { isArray, each } from 'lodash';
    import { ref, computed } from "vue";
    import { useForm } from '@inertiajs/vue3';

    export default {
        components: {
            BizButton,
            BizButtonLink,
            BizDropdown,
            BizDropdownItem,
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
            statusOptions: { type: Object, required: true },
            locationOptions: { type: Object, required: true },
            i18n: { type: Object, default: () => ({
                search : 'Search',
                any : 'Any',
                status : 'Status',
                name : 'Name',
                location : 'Location',
                user : 'User',
                date : 'Date',
                timezone : 'Timezone',
                time : 'Time',
                check_in : 'Check-in',
                actions : 'actions',
                reschedule : 'Reschedule',
                cancel : 'Cancel',
                yes : 'Yes for sure',
                cancel_event :'Cancel event',
            }) },
        },

        setup(props) {
            const form = {
                message: null,
            };

            const queryParams = computed(() => props.pageQueryParams);

            const country = queryParams.value?.country ?? null;
            const city = queryParams.value?.city ?? null;
            const location = country
                ? country + (city ? '-' + city : '')
                : null;

            return {
                queryParams: ref({
                    ...{},
                    ...queryParams.value
                }),
                dates: ref(
                    isArray(queryParams.value?.dates)
                        ? queryParams.value?.dates.filter(Boolean)
                        : []
                ),
                status: ref(queryParams.value?.status ?? null),
                term: ref(queryParams.value?.term ?? null),
                location: ref(location),
                form: useForm(form),
                selectedOrder: ref(null),
                icon,
            };
        },

        computed: {
            computedLocationOptions() {
                const options = [];

                each(this.locationOptions, (location, key) => {
                    options.push({
                        id: key,
                        value: location.country,
                    });

                    each(location.cities, (city) => {
                        options.push({
                            id: key +'-'+ city,
                            value: ' - '+ city,
                        });
                    });
                });

                return options;
            },

            locationParts() {
                const countryCity = {
                    country: null,
                    city: null,
                };

                if (!this.location) {
                    return countryCity;
                }

                const locationParts = this.location.split('-');

                return {
                    country: locationParts[0] ?? "",
                    city: locationParts[1] ?? "",
                };
            },
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

            onLocationChanged() {
                this.queryParams['city'] = this.locationParts.city;
                this.queryParams['country'] = this.locationParts.country;
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
        },
    };
</script>
