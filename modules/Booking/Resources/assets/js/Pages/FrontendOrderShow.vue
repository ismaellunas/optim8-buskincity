<template>
    <div class="columns is-multiline is-mobile is-centered box">
        <div class="column is-11-desktop is-12-tablet is-12-desktop">
            <biz-breadcrumbs
                :breadcrumbs="breadcrumbs"
            />
        </div>

        <div class="column is-11-desktop is-12-tablet is-12-desktop">
            <div class="columns is-multiline is-mobile">
                <div class="column is-7-desktop is-7-tablet is-12-mobile">
                    <h1 class="title is-2 mt-5 mb-2">
                        {{ order.product.name }}
                    </h1>

                    <p class="is-size-7">
                        <biz-tag class="is-medium">
                            {{ order.event.status }}
                        </biz-tag>
                    </p>

                    <event-detail-table
                        class="mt-3"
                        :check-in-time="checkInTime"
                        :event="order.event"
                        :location="order.location"
                        :product="order.product"
                    />
                </div>

                <div class="column is-5-desktop is-5-tablet is-12-mobile">
                    <div class="columns is-multiline is-mobile is-centered mt-5">
                        <div
                            v-if="mapPosition.latitude && mapPosition.latitude"
                            class="column is-10-desktop is-12-tablet is-12-mobile"
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

                        <div
                            v-if="can.reschedule"
                            class="column is-8-desktop is-12-tablet is-12-mobile"
                        >
                            <biz-button-link
                                class="is-fullwidth is-warning"
                                :href="route(baseRouteName+'.reschedule', order.id)"
                            >
                                <biz-icon :icon="icon.recycle" />
                                <span>Reschedule</span>
                            </biz-button-link>
                        </div>

                        <div
                            v-if="can.cancel"
                            class="column is-8-desktop is-12-tablet is-12-mobile"
                        >
                            <biz-button-icon
                                class="is-fullwidth is-danger"
                                type="button"
                                :icon="icon.remove"
                                @click="openModal"
                            >
                                <span>Cancel</span>
                            </biz-button-icon>
                        </div>

                        <div
                            v-if="can.checkIn"
                            class="column is-8-desktop is-12-tablet is-12-mobile"
                        >
                            <biz-button-icon
                                class="is-fullwidth"
                                type="button"
                                :icon="icon.buildingCheck"
                                @click="checkIn"
                            >
                                <span>Check-in</span>
                            </biz-button-icon>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <modal-cancel-event-confirmation
            v-if="isModalOpen"
            v-model="form.message"
            title="Cancel Event"
            :event="order.event"
            :product-name="order.product.name"
            @close="closeModal()"
        >
            <template #actions>
                <biz-button
                    class="is-danger ml-1"
                    type="button"
                    @click="cancel()"
                >
                    Yes for sure
                </biz-button>
            </template>
        </modal-cancel-event-confirmation>
    </div>
</template>

<script>
    import MixinHasLoader from '@/Mixins/HasLoader';
    import MixinHasModal from '@/Mixins/HasModal';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import Layout from '@/Layouts/User.vue';
    import BizBreadcrumbs from '@/Biz/Breadcrumbs.vue';
    import BizButton from '@/Biz/Button.vue';
    import BizButtonIcon from '@/Biz/ButtonIcon.vue';
    import BizButtonLink from '@/Biz/ButtonLink.vue';
    import BizGmapMarker from '@/Biz/GmapMarker.vue';
    import BizIcon from '@/Biz/Icon.vue';
    import BizTag from '@/Biz/Tag.vue';
    import EventDetailTable from '@booking/Pages/TableEventDetail.vue';
    import ModalCancelEventConfirmation from '@booking/Pages/ModalCancelEventConfirmation.vue';
    import icon from '@/Libs/icon-class';
    import { confirm as confirmAlert, oops as oopsAlert, success as successAlert, confirmDelete } from '@/Libs/alert';
    import { has } from 'lodash';
    import { useForm } from '@inertiajs/vue3';

    export default {
        components: {
            BizBreadcrumbs,
            BizButton,
            BizButtonIcon,
            BizButtonLink,
            BizGmapMarker,
            BizIcon,
            BizTag,
            EventDetailTable,
            ModalCancelEventConfirmation,
        },

        mixins: [
            MixinHasLoader,
            MixinHasModal,
            MixinHasPageErrors,
        ],

        layout: Layout,

        props: {
            breadcrumbs: { type: Array, default: () => [] },
            baseRouteName: { type: String, required: true },
            can: { type: Object, required: true },
            description: { type: String, required: true },
            order: { type: Object, required: true },
            checkInTime: { type: [String, null], required: true },
            googleApiKey: { type: String, default: null },
        },

        setup(props) {
            const form = {
                message: null,
            };

            return {
                form: useForm(form),
                icon,
                mapPosition: {
                    latitude: props.order?.location?.latitude,
                    longitude: props.order?.location?.longitude
                },
            };
        },

        methods: {
            cancel() {
                const self = this;

                self.form.post(
                    route(self.baseRouteName + '.cancel', self.order.id),
                    {
                        onStart: () => self.onStartLoadingOverlay(),
                        onFinish: () => self.onEndLoadingOverlay(),
                        onError: (errors) => {
                            oopsAlert();
                        },
                        onSuccess: (page) => {
                            self.closeModal();

                            successAlert(page.props.flash.message);
                        },
                    }
                );
            },

            onShownModal() { /* @see MixinHasModal */
                this.form.reset();
            },

            submitCheckIn(position) {
                const self = this;
                const url = route(self.baseRouteName + '.check-in', self.order.id);

                const data = {
                    geolocation: {
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude,
                    },
                };

                this.$inertia.post(
                    url,
                    data,
                    {
                        onStart: () => self.onStartLoadingOverlay(),
                        onFinish: () => self.onEndLoadingOverlay(),
                        onError: (errors) => {
                            const options = {};

                            if (has(errors, 'default.geolocation')) {
                                options.html = self.error('geolocation');
                            }

                            oopsAlert(options);
                        },
                        onSuccess: (page) => {
                            successAlert(page.props.flash.message);
                        },
                    }
                );
            },

            checkIn() {
                const self = this;

                confirmAlert(
                    "Check-in Location",
                    "Are you sure you want to check-in your current location?",
                    "Continue"
                )
                    .then((result) => {
                        if (result.isConfirmed) {
                            const successCallback = (position) => {
                                self.submitCheckIn(position);
                            };

                            const errorCallback = (error) => {
                                oopsAlert({text: "Please enable location permission to check-in."});
                            };

                            navigator.geolocation.getCurrentPosition(successCallback, errorCallback);
                        }
                    });
            },
        },
    };
</script>
