<template>
    <div>
        <div class="columns box">
            <div class="column is-4">
                <h4 class="title is-4">
                    {{ product.name }}
                </h4>

                <table class="table is-fullwidth">
                    <tr>
                        <th><biz-icon :icon="ecommerceIcon.duration" /></th>
                        <td>{{ firstEvent.duration }}</td>
                    </tr>
                    <tr>
                        <th><biz-icon :icon="ecommerceIcon.calendar" /></th>
                        <td>{{ firstEvent.start_end_time }}, {{ firstEvent.booked_date }}</td>
                    </tr>
                    <tr>
                        <th><biz-icon :icon="ecommerceIcon.timezone" /></th>
                        <td>{{ firstEvent.timezone }}</td>
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
            @close="closeModal()"
        >
            <template #event>
                <h5 class="title is-5">
                    {{ product.name }}
                </h5>

                <table class="table">
                    <tr>
                        <th><biz-icon :icon="ecommerceIcon.duration" /></th>
                        <td>{{ firstEvent.duration }}</td>
                    </tr>
                    <tr>
                        <th><biz-icon :icon="ecommerceIcon.timezone" /></th>
                        <td>{{ firstEvent.timezone }}</td>
                    </tr>
                    <tr>
                        <th><biz-icon :icon="ecommerceIcon.calendar" /></th>
                        <td><b>{{ rescheduleDateTime }}</b></td>
                    </tr>
                </table>
            </template>

            <template #reschedule>
                <table class="table">
                    <tr>
                        <th><s><biz-icon :icon="ecommerceIcon.calendar" /></s></th>
                        <td><s>{{ firstEvent.start_end_time }}, {{ firstEvent.booked_date }}</s></td>
                    </tr>
                </table>
            </template>

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
    import BizIcon from '@/Biz/Icon';
    import BookingTime from './BookingTime';
    import MixinHasLoader from '@/Mixins/HasLoader';
    import MixinHasModal from '@/Mixins/HasModal';
    import ModalTimeConfirmation from './ModalTimeConfirmation';
    import ecommerceIcon from '../Libs/ecommerce-icon';
    import moment from 'moment';
    import { reactive, ref } from 'vue';
    import { success as successAlert } from '@/Libs/alert';
    import { useForm } from '@inertiajs/inertia-vue3';

    export default {
        components: {
            BizButton,
            BizButtonLink,
            BizIcon,
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
                firstEvent: props.order.lines[0].event,
                ecommerceIcon,
            };
        },

        data() {
            return {
                availableTimes: [],
            };
        },

        computed: {
            rescheduleDateTime() {
                if (! (this.form.date && this.form.time)) {
                    return null;
                }

                const startTime = moment(
                    moment(this.form.date).format('YYYY-MM-DD') + ' ' + this.form.time
                );

                const endTime = moment(startTime).add(
                    this.firstEvent.duration_details.unit,
                    this.firstEvent.duration_details.duration
                );

                return (
                    startTime.format('k:mm')
                    + ' - ' + endTime.format('k:mm')
                    + ', ' + startTime.format('D MMMM YYYY')
                );
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
