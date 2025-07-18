<template>
    <div class="columns is-multiline is-mobile is-centered box">
        <div class="column is-11-desktop is-12-tablet is-12-mobile">
            <nav
                class="breadcrumb"
                aria-label="breadcrumbs"
            >
                <ul>
                    <li>
                        <biz-link :href="route(baseRouteName+'.index')">
                            {{ i18n.products }}
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

        <div class="column is-11-desktop is-12-tablet is-12-mobile">
            <h1 class="title is-2 mt-5 mb-2">
                {{ product.name }}
            </h1>

            <p class="is-size-7">
                <biz-tag>{{ product.sku }}</biz-tag>
            </p>

            <div class="columns is-multiline is-mobile mt-3">
                <div class="column is-8-desktop is-8-tablet is-12-mobile">
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

                <div class="column is-4-desktop is-4-tablet is-12-mobile">
                    <biz-table is-fullwidth>
                        <tbody>
                            <tr>
                                <th>Duration</th>
                                <td>{{ event.duration }}</td>
                            </tr>
                            <tr>
                                <th>Timezone</th>
                                <td>{{ event.display_timezone }}</td>
                            </tr>
                        </tbody>
                    </biz-table>
                </div>
            </div>

            <div
                v-if="hasProductGallery"
                class="columns is-multiline is-mobile mt-5"
            >
                <div class="column is-12-desktop is-12-tablet is-12-mobile">
                    <h2 class="title is-3">
                        Gallery
                    </h2>
                </div>

                <div class="column is-5-desktop is-5-tablet is-12-mobile">
                    <div class="card">
                        <div class="card-image">
                            <biz-image
                                ratio="is-3by2"
                                :src="selectedImageUrl"
                            />
                        </div>
                    </div>
                </div>

                <div class="column is-7-desktop is-7-tablet is-12-mobile">
                    <div class="columns is-multiline is-mobile">
                        <div
                            v-for="(image, imageIndex) in product.gallery"
                            :key="image.id"
                            class="column is-4-desktop is-6-tablet is-6-mobile"
                        >
                            <div class="card">
                                <div class="card-image">
                                    <biz-image
                                        ratio="is-3by2"
                                        :src="image.thumbnail_url"
                                        alt=""
                                        @click="selectedImageId = imageIndex"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="columns is-multiline is-mobile mt-5">
                <div class="column is-12-desktop is-12-tablet is-12-mobile">
                    <h2 class="title is-3">
                        {{ startCase(i18n.event_booking) }}
                    </h2>
                </div>

                <div
                    v-if="mapPosition.latitude && mapPosition.longitude"
                    class="column is-4-desktop is-12-tablet is-12-mobile"
                >
                    <div class="card">
                        <biz-gmap-marker
                            v-model="mapPosition"
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
                                    :available-times-param="{product: product.id}"
                                    :available-times-route="availableTimesRouteName"
                                    :max-date="maxDate"
                                    :min-date="minDate"
                                    :product-id="product.id"
                                    :timezone="event.display_timezone"
                                    @on-time-confirmed="openModal"
                                />
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
                {{ product.name }}
            </h5>

            <biz-table is-fullwidth>
                <tr>
                    <th><biz-icon :icon="icon.duration" /></th>
                    <td>{{ event.duration }}</td>
                </tr>
                <tr>
                    <th><biz-icon :icon="icon.timezone" /></th>
                    <td>{{ event.display_timezone }}</td>
                </tr>
                <tr>
                    <th><biz-icon :icon="icon.calendar" /></th>
                    <td><b>{{ bookedAt }}</b></td>
                </tr>
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
    import BizButton from '@/Biz/Button.vue';
    import BizGmapMarker from '@/Biz/GmapMarker.vue';
    import BizIcon from '@/Biz/Icon.vue';
    import BizImage from '@/Biz/Image.vue';
    import BizLink from '@/Biz/Link.vue';
    import BizModalCard from '@/Biz/ModalCard.vue';
    import BizTable from '@/Biz/Table.vue';
    import BizTag from '@/Biz/Tag.vue';
    import BookingTime from '@booking/Pages/BookingTime.vue';
    import Layout from '@/Layouts/User.vue';
    import MixinHasLoader from '@/Mixins/HasLoader';
    import MixinHasModal from '@/Mixins/HasModal';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import moment from 'moment';
    import { calendar, duration, timezone } from '@/Libs/icon-class';
    import { oops as oopsAlert, success as successAlert } from '@/Libs/alert';
    import { ref, computed } from 'vue';
    import { startCase } from 'lodash';
    import { useForm } from '@inertiajs/vue3';

    export default {
        components: {
            BizButton,
            BizIcon,
            BizImage,
            BizLink,
            BizModalCard,
            BizTable,
            BizTag,
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
            timezone: { type: String, required: true },
            googleApiKey: { type: String, default: null },
            i18n: {
                type: Object,
                default: () => ({
                    products: 'Products',
                })
            },
        },

        setup(props) {
            const form = {
                date: null,
                time: null,
                timezone: computed(() => props.timezone).value,
            };

            return {
                icon: { calendar, duration, timezone },
                form: useForm(form),
                isShortDescription: ref(true),
                selectedImageId: ref(null),
                mapPosition: {
                    latitude: props.event.location?.latitude,
                    longitude: props.event.location?.longitude,
                },
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

            hasProductGallery() {
                return this.product.gallery.length > 0;
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
                        route('booking.orders.book-event', self.product.id),
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

            startCase,
        },
    };
</script>
