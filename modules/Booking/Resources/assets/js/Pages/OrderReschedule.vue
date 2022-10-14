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
                    :allowed-dates-route="allowedDatesRouteName"
                    :available-times-param="{order: order.id}"
                    :available-times-route="availableTimesRouteName"
                    :max-date="maxDate"
                    :min-date="minDate"
                    :product-id="product.id"
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
    import BookingTime from '@booking/Pages/BookingTime';
    import MixinHasLoader from '@/Mixins/HasLoader';
    import MixinHasModal from '@/Mixins/HasModal';
    import ModalTimeConfirmation from '@booking/Pages/ModalTimeConfirmation';
    import TableEventRescheduleDetail from '@booking/Pages/TableEventRescheduleDetail';
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
            allowedDatesRouteName: { type: String, default: "ecommerce.products.allowed-dates" },
            availableTimesRouteName: { type: String, required: true },
            baseRouteName: { type: String, required: true },
            formatDateIso: { type: String, default: 'YYYY-MM-DD' },
            maxDate: { type: String, required: true },
            minDate: { type: String, required: true },
            order: { type: Object, required: true },
            product: { type: Object, required: true },
            timezone: { type: String, required: true },
        },

        setup(props) {
            const form = {
                date: null,
                time: null,
                timezone: props.timezone,
                message: null,
            };

            return {
                form: useForm(form),
                firstEvent: props.order.event,
                scheduleTimezone: ref(props.timezone),
            };
        },

        methods: {
            reschedule() {
                const self = this;

                self.form
                    .transform((data) => {
                        data.date = moment(data.date).format(self.formatDateIso);

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
            },
        },
    };
</script>
