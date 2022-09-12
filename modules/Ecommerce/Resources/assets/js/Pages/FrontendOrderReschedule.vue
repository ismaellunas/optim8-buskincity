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
                            {{ order.product_name }}
                        </biz-link>
                    </li>
                    <li class="is-active">
                        <a
                            href="#"
                            aria-current="page"
                        >
                            Reschedule
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
        <div class="column is-11">
            <div class="columns is-multiline mt-3">
                <div class="column is-4">
                    <table class="table">
                        <tr>
                            <th><biz-icon :icon="ecommerceIcon.duration" /></th>
                            <td>{{ order.event_duration }}</td>
                        </tr>
                        <tr>
                            <th><biz-icon :icon="ecommerceIcon.calendar" /></th>
                            <td>{{ order.event_start_end_time }}, {{ order.event_date }}</td>
                        </tr>
                        <tr>
                            <th><biz-icon :icon="ecommerceIcon.timezone" /></th>
                            <td>{{ order.timezone }}</td>
                        </tr>
                    </table>
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
        </div>

        <modal-time-confirmation
            v-if="isModalOpen"
            title="Reschedule Event"
            @close="closeModal()"
        >
            <template #event>
                <h5 class="title is-5">
                    {{ order.product_name }}
                </h5>

                <table class="table">
                    <tr>
                        <th><biz-icon :icon="ecommerceIcon.duration" /></th>
                        <td>{{ order.event_duration }}</td>
                    </tr>
                    <tr>
                        <th><biz-icon :icon="ecommerceIcon.timezone" /></th>
                        <td>{{ order.timezone }}</td>
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
                        <td><s>{{ order.event_start_end_time }}, {{ order.event_date }}</s></td>
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
    import BizButton from '@/Biz/Button';
    import BizIcon from '@/Biz/Icon';
    import BizLink from '@/Biz/Link';
    import BookingTime from './BookingTime';
    import Layout from '@/Layouts/User';
    import MixinHasLoader from '@/Mixins/HasLoader';
    import MixinHasModal from '@/Mixins/HasModal';
    import ModalTimeConfirmation from './ModalTimeConfirmation';
    import moment from 'moment';
    import ecommerceIcon from '../Libs/ecommerce-icon';
    import { reactive, ref } from 'vue';
    import { success as successAlert } from '@/Libs/alert';
    import { useForm } from '@inertiajs/inertia-vue3';

    export default {
        components: {
            BizButton,
            BizIcon,
            BizLink,
            BookingTime,
            ModalTimeConfirmation,
        },

        mixins: [
            MixinHasLoader,
            MixinHasModal,
        ],

        layout: Layout,

        props: {
            availableTimesRouteName: { type: String, required: true },
            baseRouteName: { type: String, required: true },
            disabledDates: { type: Array, default: () => [] },
            maxDate: { type: String, required: true },
            minDate: { type: String, required: true },
            order: { type: Object, required: true },
        },

        setup(props) {
            const form = {
                date: null,
                time: null,
                timezone: props.order.timezone,
            };

            const options = {
                minDate: props.minDate,
                maxDate: props.maxDate,
                disabledDates: props.disabledDates,
                color: 'link',
                showTodayButton: false,
            };

            const productDetail = {
                name: props.product_name,
                identifier: null,
            };

            return {
                form: useForm(form),
                options: reactive(options),
                availableTimes: ref([]),
                details: ref([]),
                productDetail,
                ecommerceIcon,
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
                    this.order.event_duration_details.unit,
                    this.order.event_duration_details.duration,
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
                    route(self.availableTimesRouteName, {
                        product: this.order.product_id,
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
