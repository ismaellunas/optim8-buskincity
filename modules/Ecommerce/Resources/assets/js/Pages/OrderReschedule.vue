<template>
    <div>
        <div class="columns box">
            <div class="column is-4">
                <h4 class="title is-4">
                    {{ product.name }}
                </h4>

                <table class="table is-fullwidth is-bordered">
                    <tr>
                        <th>SKU</th>
                        <td>{{ order.lines[0].identifier }}</td>
                    </tr>

                    <tr>
                        <th>Short Description</th>
                        <td>{{ product.short_description }}</td>
                    </tr>
                    <tr>
                        <th>Booked At</th>
                        <td>{{ order.lines[0].event.booked_at }}</td>
                    </tr>
                    <tr>
                        <th>Duration</th>
                        <td>{{ order.lines[0].event.duration }}</td>
                    </tr>
                </table>

                <div class="buttons">
                    <biz-button-link
                        :href="route(baseRouteName + '.show', order.id)"
                    >
                        Back
                    </biz-button-link>
                </div>
            </div>

            <div class="column is-8">
                <booking-time
                    v-model="form"
                    :available-times="availableTimes"
                    :options="options"
                    @get-available-times="getAvailableTimes"
                    @on-time-confirmed="openModal"
                />
            </div>
        </div>

        <modal-time-confirmation
            v-if="isModalOpen"
            title="Reschedule Event"
            submit-text="Reschedule"
            :details="details"
            :product="productDetail"
            @close="closeModal()"
        >
            <template #actions>
                <biz-button
                    class="is-info ml-1"
                    type="button"
                    @click="reschedule()"
                >
                    Reschedule
                </biz-button>
            </template>
        </modal-time-confirmation>
    </div>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import BizButton from '@/Biz/Button';
    import BizButtonLink from '@/Biz/ButtonLink';
    import BookingTime from './BookingTime';
    import MixinHasLoader from '@/Mixins/HasLoader';
    import MixinHasModal from '@/Mixins/HasModal';
    import ModalTimeConfirmation from './ModalTimeConfirmation';
    import moment from 'moment';
    import { reactive, ref } from 'vue';
    import { success as successAlert } from '@/Libs/alert';
    import { useForm } from '@inertiajs/inertia-vue3';

    export default {
        components: {
            BizButton,
            BizButtonLink,
            BookingTime,
            ModalTimeConfirmation,
        },

        mixins: [
            MixinHasLoader,
            MixinHasModal,
        ],

        layout: AppLayout,

        props: {
            baseRouteName: { type: String, required: true },
            order: { type: Object, required: true },
            product: { type: Object, required: true },
            minDate: { type: String, required: true },
            maxDate: { type: String, required: true },
            disabledDates: { type: Array, default: () => [] },
            timezone: { type: String, required: true },
        },

        setup(props) {
            const form = {
                date: null,
                time: null,
                timezone: props.timezone,
            };

            const options = {
                minDate: props.minDate,
                maxDate: props.maxDate,
                disabledDates: props.disabledDates,
                color: 'link',
                showTodayButton: false,
            };

            return {
                form: useForm(form),
                options: reactive(options),
                scheduleTimezone: ref(props.timezone),
            };
        },

        data() {
            return {
                availableTimes: [],
            };
        },

        computed: {
            productDetail() {
                const line = this.order.lines[0];

                return {
                    name: line.purchasable.name,
                    identifier: line.identifier,
                };
            },

            details() {
                const event = this.order.lines[0].event;
                let rescheduleDateTime = null;

                if (this.form.date && this.form.time) {
                    const composedDateTime = (
                        moment(this.form.date).format('YYYY-MM-DD')
                        + ' '
                        + this.form.time
                    );

                    rescheduleDateTime = moment(composedDateTime)
                        .format('YYYY/MM/DD HH:mm');
                }

                return [
                    { field: "Timezone", value: event.timezone },
                    { field: "Duration", value: event.duration },
                    { field: "Booked At", value: event.booked_at },
                    { field: "Reschedule At", value: rescheduleDateTime },
                ];
            },
        },

        methods: {
            getAvailableTimes() {
                if (! this.form.date) {
                    this.availableTimes = [];
                }

                const self = this;
                const date = moment(this.form.date);

                self.onStartLoadingOverlay();

                axios.get(
                    route(this.baseRouteName + '.available-times', {
                        order: this.order.id,
                        date: date.format('YYYY-MM-DD')
                    }),
                ).then((response) => {
                    self.availableTimes = response.data;
                }).then(() => {
                    self.onEndLoadingOverlay();
                });
            },

            reschedule() {
                const self = this;

                self.form
                    .transform((data) => {
                        data.date = moment(data.date).format('YYYY-MM-DD');

                        return data;
                    })
                    .post(
                        route(self.baseRouteName + '.reschedule', self.order.id),
                        {
                            onStart: self.onStartLoadingOverlay,
                            onSuccess: (page) => {
                                successAlert(page.props.flash.message);
                            },
                            onFinish: self.onEndLoadingOverlay,
                        }
                    );
            },
        },
    };
</script>
