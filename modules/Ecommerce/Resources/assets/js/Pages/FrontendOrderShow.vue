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
                    <li class="is-active">
                        <biz-link aria-current="page">
                            {{ order.product.name }}
                        </biz-link>
                    </li>
                </ul>
            </nav>
        </div>

        <div class="column is-11">
            <h1 class="title is-2 mt-5 mb-2">
                {{ order.product.name }}
            </h1>

            <p class="is-size-7">
                <biz-tag class="is-medium">
                    {{ order.event.status }}
                </biz-tag>
            </p>

            <div class="columns is-multiline mt-3">
                <div class="column is-8">
                    <event-detail-table :event="order.event" />

                    <div class="buttons">
                        <biz-button-link :href="route(baseRouteName + '.index')">
                            Back
                        </biz-button-link>
                    </div>
                </div>

                <div class="is-4">
                    <div class="columns is-multiline is-centered">
                        <div
                            v-if="can.reschedule"
                            class="column is-8"
                        >
                            <biz-button-link
                                class="is-fullwidth"
                                :href="route(baseRouteName+'.reschedule', order.id)"
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
                                class="is-fullwidth"
                                type="button"
                                :icon="icon.remove"
                                @click="cancel"
                            >
                                <span>Cancel</span>
                            </biz-button-icon>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import BizButtonIcon from '@/Biz/ButtonIcon';
    import BizButtonLink from '@/Biz/ButtonLink';
    import BizIcon from '@/Biz/Icon';
    import BizLink from '@/Biz/Link';
    import BizTag from '@/Biz/Tag';
    import EventDetailTable from './TableEventDetail';
    import Layout from '@/Layouts/User';
    import MixinHasLoader from '@/Mixins/HasLoader';
    import icon from '@/Libs/icon-class';
    import { oops as oopsAlert, success as successAlert, confirmDelete } from '@/Libs/alert';

    export default {
        components: {
            BizButtonIcon,
            BizButtonLink,
            BizIcon,
            BizLink,
            BizTag,
            EventDetailTable,
        },

        mixins: [
            MixinHasLoader,
        ],

        layout: Layout,

        props: {
            baseRouteName: { type: String, required: true },
            can: { type: Object, required: true },
            description: { type: String, required: true },
            order: { type: Object, required: true },
        },

        setup(props) {
            return {
                icon,
            };
        },

        methods: {
            cancel() {
                const self = this;

                confirmDelete('Are you sure want to cancel this Booking?')
                    .then(result => {
                        if (result.isConfirmed) {
                            self.$inertia.post(
                                route(self.baseRouteName + '.cancel', self.order.id),
                                {},
                                {
                                    onStart: () => self.onStartLoadingOverlay(),
                                    onFinish: () => self.onEndLoadingOverlay(),
                                    onError: (errors) => {
                                        oopsAlert();
                                    },
                                    onSuccess: (page) => {
                                        successAlert(page.props.flash.message);
                                    },
                                }
                            );
                        }
                    });

            },
        },
    };
</script>
