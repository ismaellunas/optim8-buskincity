<template>
    <div class="box">
        <div class="columns is-multiline">
            <div class="column is-7">
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
                    :user-name="order.user_full_name"
                />
            </div>

            <div class="column is-5">
                <div class="columns is-multiline is-centered mt-5">
                    <div
                        v-if="mapPosition.latitude && mapPosition.latitude"
                        class="column is-10"
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
                        class="column is-8"
                    >
                        <biz-button-link
                            class="is-fullwidth is-warning"
                            :href="route(baseRouteName + '.reschedule', order.id)"
                        >
                            <biz-icon :icon="icon.recycle" />
                            <span>{{ i18n.reschedule }}</span>
                        </biz-button-link>
                    </div>

                    <div
                        v-if="can.cancel"
                        class="column is-8"
                    >
                        <biz-button-icon
                            class="is-fullwidth is-danger"
                            type="button"
                            :icon="icon.remove"
                            @click="openModal"
                        >
                            <span>{{ i18n.cancel }}</span>
                        </biz-button-icon>
                    </div>
                </div>
            </div>
        </div>

        <modal-cancel-event-confirmation
            v-if="isModalOpen"
            v-model="form.message"
            :title="i18n.cancel_event"
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
                    {{ i18n.yes }}
                </biz-button>
            </template>
        </modal-cancel-event-confirmation>
    </div>
</template>

<script>
    import MixinHasLoader from '@/Mixins/HasLoader';
    import MixinHasModal from '@/Mixins/HasModal';
    import AppLayout from '@/Layouts/AppLayout.vue';
    import BizButton from '@/Biz/Button.vue';
    import BizButtonIcon from '@/Biz/ButtonIcon.vue';
    import BizButtonLink from '@/Biz/ButtonLink.vue';
    import BizGmapMarker from '@/Biz/GmapMarker.vue';
    import BizIcon from '@/Biz/Icon.vue';
    import BizTag from '@/Biz/Tag.vue';
    import EventDetailTable from '@booking/Pages/TableEventDetail.vue';
    import ModalCancelEventConfirmation from '@booking/Pages/ModalCancelEventConfirmation.vue';
    import icon from '@/Libs/icon-class';
    import { oops as oopsAlert, success as successAlert } from '@/Libs/alert';
    import { useForm } from '@inertiajs/vue3';

    export default {
        components: {
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
        ],

        layout: AppLayout,

        props: {
            can: { type: Object, required: true },
            baseRouteName: { type: String, required: true },
            order: { type: Object, required: true },
            checkInTime: { type: [String, null], required: true },
            googleApiKey: { type: String, default: null },
            i18n: { type: Object, default: () => ({
                reschedule : 'Reschedule',
                cancel : 'Cancel',
                cancel_event : 'Cancel event',
                yes : 'Yes for sure',
            }) },
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
            }
        },
    };
</script>
