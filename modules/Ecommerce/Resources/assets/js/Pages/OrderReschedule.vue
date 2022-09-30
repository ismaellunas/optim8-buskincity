<template>
    <div>
        <div class="columns box">
            <div class="column is-4">
                <h4 class="title is-4">
                    {{ product.name }}
                </h4>

                <table-event-reschedule-detail :event="firstEvent" />

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
            v-model="form.message"
            title="Reschedule Event"
            :product-name="product.name"
            :event="firstEvent"
            :selected-date="form.date"
            :selected-time="form.time"
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
    import TableEventRescheduleDetail from './TableEventRescheduleDetail';
    import ecommerceIcon from '../Libs/ecommerce-icon';
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
            TableEventRescheduleDetail,
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
                message: null,
            };

            const options = {
                minDate: props.minDate,
                maxDate: props.maxDate,
                disabledDates: props.disabledDates,
                color: 'link',
                showTodayButton: false,
            };

            return {
                availableTimes: ref([]),
                form: useForm(form),
                ecommerceIcon,
                firstEvent: props.order.event,
                options: reactive(options),
                scheduleTimezone: ref(props.timezone),
            };
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

            onShownModal() { /* @see MixinHasModal */
                this.form.message = null;
            }
        },
    };
</script>
