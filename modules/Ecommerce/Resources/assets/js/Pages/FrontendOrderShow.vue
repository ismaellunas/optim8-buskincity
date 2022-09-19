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
                        <a
                            href="#"
                            aria-current="page"
                        >
                            {{ order.product_name }}
                        </a>
                    </li>
                </ul>
            </nav>
        </div>

        <div class="column is-11">
            <h1 class="title is-2 mt-5 mb-2">
                {{ order.product_name }}
            </h1>

            <p class="is-size-7">
                <biz-tag class="is-medium">
                    {{ order.status }}
                </biz-tag>
            </p>

            <div class="columns is-multiline mt-3">
                <div class="column is-8">
                    <biz-table class="is-fullwidth">
                        <tr>
                            <th><biz-icon :icon="ecommerceIcon.calendar" /></th>
                            <td>{{ order.event_start_end_time }}, {{ order.event_date }}</td>
                        </tr>
                        <tr>
                            <th><biz-icon :icon="ecommerceIcon.timezone" /></th>
                            <td>{{ order.timezone }} ({{ order.timezoneOffset }})</td>
                        </tr>
                    </biz-table>
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
    import BizTable from '@/Biz/Table';
    import BizTag from '@/Biz/Tag';
    import Layout from '@/Layouts/User';
    import MixinHasLoader from '@/Mixins/HasLoader';
    import ecommerceIcon from '../Libs/ecommerce-icon';
    import icon from '@/Libs/icon-class';
    import { oops as oopsAlert, success as successAlert, confirmDelete } from '@/Libs/alert';

    export default {
        components: {
            BizButtonIcon,
            BizButtonLink,
            BizIcon,
            BizLink,
            BizTable,
            BizTag,
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
                ecommerceIcon,
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
