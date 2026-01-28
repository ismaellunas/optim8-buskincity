<template>
    <div class="columns is-multiline is-mobile is-centered box">
        <div class="column is-11-desktop is-12-tablet is-12-mobile">
            <nav
                class="breadcrumb"
                aria-label="breadcrumbs"
            >
                <ul>
                    <li>
                        <biz-link :href="route('booking.products.index')">
                            {{ i18n.events }}
                        </biz-link>
                    </li>
                    <li class="is-active">
                        <a
                            href="#"
                            aria-current="page"
                        >
                            {{ event.title }}
                        </a>
                    </li>
                </ul>
            </nav>
        </div>

        <div class="column is-11-desktop is-12-tablet is-12-mobile">
            <h1 class="title is-2 mt-5 mb-2">
                {{ event.title }}
            </h1>
            <h2 class="subtitle is-4 has-text-grey">
                {{ pitchName }}
            </h2>

            <div class="columns is-multiline is-mobile mt-3">
                <div class="column is-8-desktop is-8-tablet is-12-mobile">
                    <div class="content">
                        <p>
                            {{ event.description || event.excerpt }}
                        </p>
                    </div>
                </div>

                <div class="column is-4-desktop is-4-tablet is-12-mobile">
                    <biz-table is-fullwidth>
                        <tbody>
                            <tr>
                                <th>Event Dates</th>
                                <td>{{ displayDateRange }}</td>
                            </tr>
                            <tr>
                                <th>Duration</th>
                                <td>{{ event.duration }}</td>
                            </tr>
                            <tr>
                                <th>Timezone</th>
                                <td>{{ timezone }}</td>
                            </tr>
                        </tbody>
                    </biz-table>
                </div>
            </div>

            <div class="columns is-multiline is-mobile mt-5">
                <div class="column is-12-desktop is-12-tablet is-12-mobile">
                    <h2 class="title is-3">
                        {{ i18n.event_booking }}
                    </h2>
                </div>

                <div
                    v-if="mapPosition.latitude && mapPosition.longitude"
                    class="column is-4-desktop is-12-tablet is-12-mobile"
                >
                    <div class="card">
                        <biz-gmap-marker
                            v-model="mapPositionLocal"
                            :api-key="googleApiKey"
                            :init-position="mapPosition"
                            :map-style="['width: 100%', 'height: 378px']"
                            :enable-search-box="false"
                            :enable-marker-move="false"
                        />
                    </div>
                </div>

                <div class="column is-8-desktop is-12-tablet is-12-mobile">
                    <div class="card">
                        <div class="card-content">
                            <div class="content">
                                <booking-time
                                    v-model="form"
                                    :allowed-dates-route="allowedDatesRouteName"
                                    :available-times-param="availableTimesParams"
                                    :available-times-route="availableTimesRouteName"
                                    :max-date="maxDate"
                                    :min-date="minDate"
                                    :event-id="event.id"
                                    :multi-select="true"
                                    :timezone="timezone"
                                />
                                <div class="mt-4">
                                    <biz-button
                                        class="is-link"
                                        :disabled="!hasSelectedSlots"
                                        @click="openModal"
                                    >
                                        Review & Book
                                    </biz-button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <biz-modal-card
            v-if="isModalOpen"
            @close="closeModal"
        >
            <template #header>
                <p class="modal-card-title has-text-weight-bold">
                    {{ startCase(i18n.booking_event_confirmation) }}
                </p>

                <button
                    aria-label="close"
                    class="delete"
                    @click="closeModal"
                />
            </template>

            <h5 class="title is-5">
                {{ event.title }}
            </h5>

            <biz-table is-fullwidth>
                <tr>
                    <th><biz-icon :icon="icon.duration" /></th>
                    <td>{{ event.duration }}</td>
                </tr>
                <tr>
                    <th><biz-icon :icon="icon.timezone" /></th>
                    <td>{{ timezone }}</td>
                </tr>
                <tr>
                    <th><biz-icon :icon="icon.calendar" /></th>
                    <td><b>{{ selectedSlotsLabel }}</b></td>
                </tr>
            </biz-table>

            <div v-if="hasSelectedSlots" class="content">
                <p class="has-text-weight-bold mb-2">Selected time slots</p>
                <ul>
                    <li v-for="slot in selectedSlotsDisplay" :key="slot.key">
                        {{ slot.label }}
                    </li>
                </ul>
            </div>

            <template #footer>
                <div
                    class="columns mx-0"
                    style="width: 100%"
                >
                    <div class="column px-0">
                        <div class="is-pulled-right">
                            <biz-button @click="closeModal">
                                Cancel
                            </biz-button>
                            <biz-button
                                class="is-link"
                                :disabled="!hasSelectedSlots"
                                @click="submitBooking"
                            >
                                Book
                            </biz-button>
                        </div>
                    </div>
                </div>
            </template>
        </biz-modal-card>
    </div>
</template>

<script>
    import BizButton from '@/Biz/Button.vue';
    import BizGmapMarker from '@/Biz/GmapMarker.vue';
    import BizIcon from '@/Biz/Icon.vue';
    import BizLink from '@/Biz/Link.vue';
    import BizModalCard from '@/Biz/ModalCard.vue';
    import BizTable from '@/Biz/Table.vue';
    import BookingTime from '@booking/Pages/BookingTime.vue';
    import Layout from '@/Layouts/User.vue';
    import MixinHasLoader from '@/Mixins/HasLoader';
    import MixinHasModal from '@/Mixins/HasModal';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import moment from 'moment';
    import { calendar, duration, timezone } from '@/Libs/icon-class';
    import { oops as oopsAlert, success as successAlert } from '@/Libs/alert';
    import { computed, ref } from 'vue';
    import { startCase } from 'lodash';
    import { useForm } from '@inertiajs/vue3';

    export default {
        components: {
            BizButton,
            BizIcon,
            BizLink,
            BizModalCard,
            BizTable,
            BookingTime,
            BizGmapMarker,
        },

        mixins: [
            MixinHasLoader,
            MixinHasModal,
            MixinHasPageErrors,
        ],

        layout: Layout,

        props: {
            allowedDatesRouteName: { type: String, required: true },
            availableTimesRouteName: { type: String, required: true },
            baseRouteName: { type: String, required: true },
            event: { type: Object, required: true },
            maxDate: { type: String, required: true },
            minDate: { type: String, required: true },
            product: { type: Object, required: true },
            pitchName: { type: String, required: true },
            mapPosition: { type: Object, required: true },
            timezone: { type: String, required: true },
            googleApiKey: { type: String, default: null },
            i18n: { type: Object, required: true },
        },

        setup(props) {
            const mapPositionLocal = ref({
                latitude: props.mapPosition?.latitude ?? null,
                longitude: props.mapPosition?.longitude ?? null,
            });

            const form = useForm({
                date: null,
                time: null,
                timezone: props.timezone,
                product_event_id: props.event.id,
                selected_slots: [],
            });

            const availableTimesParams = computed(() => ({
                event: props.event.id,
            }));

            return {
                icon: { calendar, duration, timezone },
                form,
                availableTimesParams,
                isShortDescription: ref(true),
                mapPositionLocal,
            };
        },

        computed: {
            hasSelectedSlots() {
                return (this.form.selected_slots ?? []).length > 0;
            },

            selectedSlotsDisplay() {
                return (this.form.selected_slots ?? [])
                    .map((slot) => {
                        const dateLabel = moment(slot.date).format('MMM D, YYYY');
                        return {
                            key: `${slot.date}-${slot.time}`,
                            label: `${dateLabel} ${slot.time}`,
                        };
                    })
                    .sort((a, b) => a.key.localeCompare(b.key));
            },

            selectedSlotsLabel() {
                if (!this.hasSelectedSlots) {
                    return 'No slots selected';
                }

                return `${this.form.selected_slots.length} slot(s) selected`;
            },

            displayDateRange() {
                const start = moment(this.event.started_at).format('MMM D, YYYY');
                const end = moment(this.event.ended_at).format('MMM D, YYYY');
                return `${start} - ${end}`;
            },
        },

        methods: {
            submitBooking() {
                const self = this;
                const slots = (self.form.selected_slots ?? []).map((slot) => ({
                    date: moment(slot.date).format('YYYY-MM-DD'),
                    time: slot.time,
                }));

                if (!slots.length) {
                    return;
                }

                self.form
                    .transform((data) => {
                        data.product_event_id = self.event.id;
                        data.slots = slots;

                        return data;
                    })
                    .post(
                        route('booking.orders.book-event-batch', self.product.id),
                        {
                            onStart: () => self.onStartLoadingOverlay(),
                            onSuccess: (page) => {
                                successAlert(page.props.flash.message);
                                self.form.selected_slots = [];
                                self.closeModal();
                            },
                            onError: (errors) => {
                                const validationMessages = [
                                    self.error('slots', 'default', errors),
                                ].filter(Boolean);

                                oopsAlert({ html: validationMessages.join('<br>') });
                            },
                            onFinish: () => {
                                self.onEndLoadingOverlay();
                            },
                        }
                    );
            },

            startCase,
        },
    };
</script>
