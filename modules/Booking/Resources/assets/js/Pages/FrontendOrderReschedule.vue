<template>
    <div class="columns is-multiline is-centered box">
        <div class="column is-11">
            <nav
                class="breadcrumb"
                aria-label="breadcrumbs"
            >
                <ul>
                    <li>
                        <biz-link :href="route(baseRouteName+'.index')">
                            Events
                        </biz-link>
                    </li>
                    <li>
                        <biz-link :href="route(baseRouteName+'.show', order.id)">
                            {{ order.product.name }}
                        </biz-link>
                    </li>
                    <li class="is-active">
                        <biz-link aria-current="page">
                            Reschedule
                        </biz-link>
                    </li>
                </ul>
            </nav>
        </div>
        <div class="column is-11">
            <div class="columns is-multiline mt-3">
                <div class="column is-4">
                    <table-event-reschedule-detail :event="order.event" />

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
                        :available-times-param="{product: order.product.id}"
                        :available-times-route="availableTimesRouteName"
                        :max-date="maxDate"
                        :min-date="minDate"
                        :product-id="order.product.id"
                        @on-time-confirmed="openModal"
                    />
                </div>
            </div>
        </div>

        <modal-time-confirmation
            v-if="isModalOpen"
            v-model="form.message"
            title="Reschedule Event"
            :event="order.event"
            :product-name="order.product.name"
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
    import BizButton from '@/Biz/Button';
    import BizButtonLink from '@/Biz/ButtonLink';
    import BizIcon from '@/Biz/Icon';
    import BizLink from '@/Biz/Link';
    import BookingTime from '@ecommerce/Pages/BookingTime';
    import Layout from '@/Layouts/User';
    import MixinHasLoader from '@/Mixins/HasLoader';
    import MixinHasModal from '@/Mixins/HasModal';
    import ModalTimeConfirmation from '@ecommerce/Pages/ModalTimeConfirmation';
    import TableEventRescheduleDetail from '@ecommerce/Pages/TableEventRescheduleDetail';
    import moment from 'moment';
    import { reactive, ref } from 'vue';
    import { success as successAlert } from '@/Libs/alert';
    import { useForm } from '@inertiajs/inertia-vue3';

    export default {
        components: {
            BizButton,
            BizButtonLink,
            BizLink,
            BookingTime,
            ModalTimeConfirmation,
            TableEventRescheduleDetail,
        },

        mixins: [
            MixinHasLoader,
            MixinHasModal,
        ],

        layout: Layout,

        props: {
            allowedDatesRouteName: { type: String, required: true },
            availableTimesRouteName: { type: String, required: true },
            baseRouteName: { type: String, required: true },
            maxDate: { type: String, required: true },
            minDate: { type: String, required: true },
            order: { type: Object, required: true },
        },

        setup(props) {
            const form = {
                date: null,
                time: null,
                timezone: props.order.timezone,
                message: null,
            };

            return {
                form: useForm(form),
                details: ref([]),
            };
        },

        methods: {
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
            },
        },
    };
</script>
