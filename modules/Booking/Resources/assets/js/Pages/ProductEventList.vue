<template>
    <div>
        <div class="columns">
            <div class="column has-text-right">
                <biz-button
                    type="button"
                    class="is-primary"
                    @click="openModalCreate()"
                >
                    <span class="icon is-small">
                        <i :class="icon.add" />
                    </span>
                    <span>{{ i18n.add_new }}</span>
                </biz-button>
            </div>
        </div>

        <biz-table-index
            :records="events"
            :is-ajax-pagination="true"
            :query-params="queryParams"
            @on-clicked-pagination="getRecords"
        >
            <template #thead>
                <tr>
                    <th>{{ i18n.title }}</th>
                    <th>{{ i18n.started_at }}</th>
                    <th>{{ i18n.ended_at }}</th>
                    <th>{{ i18n.status }}</th>
                    <th>
                        <div class="level-right">
                            {{ i18n.actions }}
                        </div>
                    </th>
                </tr>
            </template>

            <tr
                v-for="record in events.data"
                :key="record.id"
            >
                <td>{{ record.title }}</td>
                <td>{{ record.started_at }}</td>
                <td>{{ record.ended_at }}</td>
                <td>
                    <biz-tag
                        class="is-small is-rounded"
                        :class="{ 'is-success': record.status == 'published' }"
                    >
                        {{ record.display_status }}
                    </biz-tag>
                </td>
                <td>
                    <div class="level-right">
                        <biz-button
                            type="button"
                            class="is-ghost has-text-black"
                            @click="openSchedule(record)"
                        >
                            <span class="icon is-small">
                                <i :class="icon.calendar" />
                            </span>
                        </biz-button>

                        <biz-button
                            type="button"
                            class="is-ghost has-text-black"
                            @click="openModalEdit(record)"
                        >
                            <span class="icon is-small">
                                <i :class="icon.edit" />
                            </span>
                        </biz-button>

                        <biz-button
                            type="button"
                            class="is-ghost has-text-black ml-1"
                            @click="onDelete(record)"
                        >
                            <span class="icon is-small">
                                <i :class="icon.remove" />
                            </span>
                        </biz-button>
                    </div>
                </td>
            </tr>
        </biz-table-index>

        <product-event-form-modal
            v-if="isModalOpen"
            :selected-event="selectedEvent"
            :product="product"
            :timezone="timezone"
            :weekdays="weekdays"
            :pitch-schedule="pitchSchedule"
            @close="closeModal()"
            @after-submit="afterSubmit"
        />
    </div>
</template>

<script>
    import MixinHasLoader from '@/Mixins/HasLoader';
    import MixinHasModal from '@/Mixins/HasModal';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import BizButton from '@/Biz/Button.vue';
    import BizTableIndex from '@/Biz/TableIndex.vue';
    import BizTag from '@/Biz/Tag.vue';
    import ProductEventFormModal from './ProductEventFormModal.vue';
    import icon from '@/Libs/icon-class';
    import { confirmDelete, oops as oopsAlert, success as successAlert } from '@/Libs/alert';

    export default {
        name: 'ProductEventList',

        components: {
            BizButton,
            BizTableIndex,
            BizTag,
            ProductEventFormModal,
        },

        mixins: [
            MixinHasLoader,
            MixinHasModal,
            MixinHasPageErrors,
        ],

        inject: {
            i18n: { default: () => ({
                add_new: 'Add new',
                title: 'Title',
                started_at: 'Started at',
                ended_at: 'Ended at',
                actions: 'Actions',
                add_new_event: 'Add new event',
                started_and_ended_at: 'Started at and ended at',
                description: 'Description',
                are_you_sure: 'Are you sure?',
            }) }
        },

        props: {
            product: { type: Object, required: true },
            timezone: { type: String, default: 'UTC' },
            weekdays: { type: Object, required: true },
            pitchSchedule: { 
                type: Object, 
                default: () => ({
                    timezone: 'Not set',
                    dateRange: 'Not set',
                    availableDays: 'Not set',
                    availableHours: 'Not set',
                    startDate: null,
                    endDate: null,
                    availableDaysArray: [],
                    weeklyHoursData: {},
                })
            },
        },

        data() {
            return {
                baseRouteName: 'admin.booking.products.product-events',
                icon,
                queryParams: {},
                selectedEvent: {},
                term: null,
                events: {
                    data: [],
                    from: 0,
                    to: 0,
                    total: 0,
                    current_page: 1,
                    last_page: 1,
                    links: [],
                },
            };
        },

        beforeMount() {
            this.getRecords();
        },

        methods: {
            getRecords(url = null) {
                const self = this;

                url = url ?? route(self.baseRouteName + '.records', this.product.id);

                console.log('ProductEventList: Fetching records from', url);
                console.log('ProductEventList: Product ID', this.product.id);

                axios.get(url, {
                    params: self.queryParams,
                }).then((response) => {
                    console.log('ProductEventList: Received response', response.data);
                    self.events = response.data;
                }).catch((error) => {
                    console.error('ProductEventList: Error fetching records', error);
                    console.error('ProductEventList: Error response', error.response);
                });
            },

            openModalEdit(event) {
                this.selectedEvent = event;

                this.openModal();
            },

            openSchedule(record) {
                window.location = route('admin.booking.products.product-events.schedule.edit', {
                    product: this.product.id,
                    productEvent: record.id,
                });
            },

            onDelete(record) {
                const self = this;

                confirmDelete(
                    self.are_you_sure,
                ).then(result => {
                    if (result.isConfirmed) {
                        const url = route('admin.booking.products.product-events.destroy', [self.product.id, record.id]);

                        self.onStartLoadingOverlay();

                        axios
                            .delete(url)
                            .then(response => {
                                self.getRecords();
                                successAlert(response.data);
                            })
                            .catch(error => {
                                oopsAlert();
                            })
                            .then(() => {
                                self.onEndLoadingOverlay();
                            });
                    }
                })
            },

            openModalCreate() {
                console.log('ProductEventList: Opening modal for create', {
                    product: this.product,
                    isModalOpen: this.isModalOpen
                });
                this.selectedEvent = null;
                this.openModal();
                console.log('ProductEventList: Modal opened', {
                    isModalOpen: this.isModalOpen
                });
            },

            afterSubmit() {
                this.getRecords();
                this.closeModal();
            },
        },
    };
</script>
