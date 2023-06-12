<template>
    <div class="columns is-multiline is-mobile is-centered box">
        <div class="column is-11-desktop is-11-tablet is-12-mobile">
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
                        <biz-link
                            aria-current="page"
                            href="#"
                        >
                            Reschedule
                        </biz-link>
                    </li>
                </ul>
            </nav>
        </div>
        <div class="column is-11-desktop is-11-tablet is-12-mobile">
            <div class="columns is-multiline is-mobile mt-3">
                <div class="column is-5-desktop is-12-tablet is-12-mobile">
                    <div class="card">
                        <div class="card-content">
                            <table-event-reschedule-detail :event="order.event" />

                            <div class="buttons">
                                <biz-button-link
                                    :href="route(baseRouteName + '.show', order.id)"
                                >
                                    Back
                                </biz-button-link>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="column is-7-desktop is-12-tablet is-12-mobile">
                    <div class="card">
                        <div class="card-content">
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
    import MixinHasLoader from '@/Mixins/HasLoader';
    import MixinHasModal from '@/Mixins/HasModal';
    import Layout from '@/Layouts/User.vue';
    import BizButton from '@/Biz/Button.vue';
    import BizButtonLink from '@/Biz/ButtonLink.vue';
    import BizIcon from '@/Biz/Icon.vue';
    import BizLink from '@/Biz/Link.vue';
    import BookingTime from '@booking/Pages/BookingTime.vue';
    import ModalTimeConfirmation from '@booking/Pages/ModalTimeConfirmation.vue';
    import TableEventRescheduleDetail from '@booking/Pages/TableEventRescheduleDetail.vue';
    import moment from 'moment';
    import { reactive, ref } from 'vue';
    import { success as successAlert } from '@/Libs/alert';
    import { useForm } from '@inertiajs/vue3';

    export default {
        name: 'FrontendOrderReschedule',

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
