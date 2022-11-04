<template>
    <div class="box">
        <div class="columns is-multiline">
            <div class="column is-12">
                <h1 class="title is-2 mt-5 mb-2">
                    {{ order.product.name }}
                </h1>

                <p class="is-size-7">
                    <biz-tag class="is-medium">
                        {{ order.event.status }}
                    </biz-tag>
                </p>
            </div>

            <div class="column is-8">
                <event-detail-table :event="order.event">
                    <tr>
                        <th><biz-icon :icon="icon.user" /></th>
                        <td>{{ order.user_full_name }}</td>
                    </tr>
                    <tr>
                        <th><biz-icon :icon="icon.buildingCheck" /></th>
                        <td>{{ checkInTime }}</td>
                    </tr>
                </event-detail-table>

                <div class="buttons">
                    <biz-button-link :href="route(baseRouteName + '.index')">
                        <biz-icon :icon="icon.back" />
                        <span>Back</span>
                    </biz-button-link>
                </div>
            </div>

            <div class="column is-4">
                <div class="columns is-multiline is-centered">
                    <div
                        v-if="can.reschedule"
                        class="column is-8"
                    >
                        <biz-button-link
                            class="is-fullwidth is-warning"
                            :href="route(baseRouteName + '.reschedule', order.id)"
                        >
                            <biz-icon :icon="icon.recycle" />
                            <span>Reschedule</span>
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
                            <span>Cancel</span>
                        </biz-button-icon>
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
    import AppLayout from '@/Layouts/AppLayout';
    import BizButton from '@/Biz/Button';
    import BizButtonIcon from '@/Biz/ButtonIcon';
    import BizButtonLink from '@/Biz/ButtonLink';
    import BizIcon from '@/Biz/Icon';
    import BizTag from '@/Biz/Tag';
    import EventDetailTable from '@booking/Pages/TableEventDetail';
    import MixinHasLoader from '@/Mixins/HasLoader';
    import MixinHasModal from '@/Mixins/HasModal';
    import ModalCancelEventConfirmation from '@booking/Pages/ModalCancelEventConfirmation';
    import icon from '@/Libs/icon-class';
    import { oops as oopsAlert, success as successAlert } from '@/Libs/alert';
    import { useForm } from '@inertiajs/inertia-vue3';

    export default {
        components: {
            BizButton,
            BizButtonIcon,
            BizButtonLink,
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
        },

        setup(props) {
            const form = {
                message: null,
            };

            return {
                form: useForm(form),
                icon,
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
