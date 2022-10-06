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
                            Products
                        </biz-link>
                    </li>
                    <li class="is-active">
                        <a
                            href="#"
                            aria-current="page"
                        >
                            {{ product.name }}
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
        <div class="column is-11">
            <h1 class="title is-2 mt-5 mb-2">
                {{ product.name }}
            </h1>

            <p class="is-size-7">
                <biz-tag>{{ product.sku }} </biz-tag>
            </p>

            <div class="columns is-multiline mt-3">
                <div class="column is-8">
                    <div class="content">
                        <p>
                            {{ isShortDescription ? product.short_description : product.description }}
                        </p>

                        <p class="buttons">
                            <biz-button
                                class="is-ghost has-text-primary has-text-weight-bold"
                                type="button"
                                @click="toggleDescription"
                            >
                                {{ isShortDescription ? 'Read more' : 'Less' }}
                            </biz-button>
                        </p>
                    </div>
                </div>

                <div class="column is-4">
                    <biz-table>
                        <tbody>
                            <tr>
                                <th>Duration</th>
                                <td>{{ event.duration }}</td>
                            </tr>
                            <tr>
                                <th>Timezone</th>
                                <td>{{ event.timezone }}</td>
                            </tr>
                        </tbody>
                    </biz-table>
                </div>
            </div>

            <div class="columns is-multiline mt-5">
                <div class="column is-12">
                    <h2 class="title is-3">
                        Gallery
                    </h2>
                </div>

                <div class="column is-5">
                    <div
                        v-if="product.gallery.length == 0"
                        class="hero is-medium is-primary is-radius"
                    >
                        <div class="hero-body" />
                    </div>

                    <div
                        v-else
                        class="card"
                    >
                        <div class="card-image">
                            <biz-image
                                ratio="is-3by2"
                                :src="selectedImageUrl"
                            />
                        </div>
                    </div>
                </div>

                <div class="column is-7">
                    <div
                        v-if="product.gallery.length > 0"
                        class="columns is-multiline"
                    >
                        <div
                            v-for="(image, imageIndex) in product.gallery"
                            :key="image.id"
                            class="column is-one-third-desktop is-half-tablet"
                        >
                            <div class="card">
                                <div class="card-image">
                                    <biz-image
                                        ratio="is-3by2"
                                        :src="image.file_url"
                                        alt=""
                                        @click="selectedImageId = imageIndex"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        v-else
                        class="hero is-medium is-primary is-radius"
                    >
                        <div class="hero-body" />
                    </div>
                </div>
            </div>

            <div class="columns is-multiline mt-5">
                <div class="column is-12">
                    <h2 class="title is-3">
                        Event Booking
                    </h2>
                </div>

                <div class="column is-8">
                    <booking-time
                        v-model="form"
                        :allowed-dates-route="allowedDatesRouteName"
                        :available-times-param="{product: product.id}"
                        :available-times-route="availableTimesRouteName"
                        :max-date="maxDate"
                        :min-date="minDate"
                        :product-id="product.id"
                        @on-time-confirmed="openModal"
                    />
                </div>
            </div>
        </div>

        <biz-modal-card
            v-if="isModalOpen"
            @close="closeModal"
        >
            <template #header>
                Booking Event Confirmation
            </template>

            <biz-table>
                <tbody>
                    <tr>
                        <th>Name</th>
                        <td>{{ product.name }}</td>
                    </tr>
                    <tr>
                        <th>Duration</th>
                        <td>{{ event.duration }}</td>
                    </tr>
                    <tr>
                        <th>Booked At</th>
                        <td>{{ bookedAt }}</td>
                    </tr>
                </tbody>
            </biz-table>

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
    import BizButton from '@/Biz/Button';
    import BizImage from '@/Biz/Image';
    import BizLink from '@/Biz/Link';
    import BizModalCard from '@/Biz/ModalCard';
    import BizTable from '@/Biz/Table';
    import BizTag from '@/Biz/Tag';
    import BookingTime from './BookingTime';
    import Layout from '@/Layouts/User';
    import MixinHasLoader from '@/Mixins/HasLoader';
    import MixinHasModal from '@/Mixins/HasModal';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import moment from 'moment';
    import { oops as oopsAlert, success as successAlert } from '@/Libs/alert';
    import { useForm } from '@inertiajs/inertia-vue3';
    import { ref } from 'vue';

    export default {
        components: {
            BizButton,
            BizImage,
            BizLink,
            BizModalCard,
            BizTable,
            BizTag,
            BookingTime,
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
            timezone: { type: String, required: true },
        },

        setup(props) {
            const form = {
                date: null,
                time: null,
                timezone: props.timezone,
            };

            return {
                form: useForm(form),
                isShortDescription: ref(true),
                selectedImageId: ref(null),
            };
        },

        computed: {
            selectedImageUrl() {
                if (this.product.gallery.length == 0) {
                    return "https://bulma.io/images/placeholders/1280x960.png";
                }

                if (!this.selectedImageId) {
                    return this.product.gallery[0].file_url;
                }

                return this.product.gallery[this.selectedImageId].file_url;
            },

            bookedAt() {
                if (this.form.date && this.form.time) {
                    const composedDateTime = (
                        moment(this.form.date).format('YYYY-MM-DD')
                        + ' '
                        + this.form.time
                    );

                    return moment(composedDateTime).format('YYYY/MM/DD HH:mm');
                }

                return null;
            },
        },

        methods: {
            toggleDescription() {
                this.isShortDescription = !this.isShortDescription;
            },

            submitBooking() {
                const self = this;

                self.form
                    .transform((data) => {
                        data.date = moment(data.date).format('YYYY-MM-DD');

                        return data;
                    })
                    .post(
                        route('ecommerce.orders.book-event', self.product.id),
                        {
                            onStart: () => self.onStartLoadingOverlay(),
                            onSuccess: (page) => {
                                successAlert(page.props.flash.message);
                            },
                            onError: (errors) => {
                                const validationMessages = [
                                    self.error('date', 'default', errors),
                                    self.error('time', 'default', errors),
                                ].filter(Boolean);

                                oopsAlert({html: validationMessages.join('<br>')});
                            },
                            onFinish: () => {
                                self.onEndLoadingOverlay();
                            },
                        }
                    );
            },
        },
    };
</script>
