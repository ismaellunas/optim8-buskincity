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
                Scheduled Event
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
                            <th>Event</th>
                            <td>{{ order.product_name }}</td>
                        </tr>
                        <tr>
                            <th>Date</th>
                            <td>{{ order.event_date }}</td>
                        </tr>
                        <tr>
                            <th>Time</th>
                            <td>{{ order.event_start_end_time }}</td>
                        </tr>
                        <tr>
                            <th>Time Zone</th>
                            <td>{{ order.timezone }} ({{ order.timezoneOffset }})</td>
                        </tr>
                    </biz-table>
                </div>

                <div class="is-4">
                    <div class="columns is-multiline is-centered">
                        <div class="column is-8">
                            <biz-button-icon
                                class="is-fullwidth"
                                type="button"
                                :icon="icon.recycle"
                            >
                                <span>Reschedule</span>
                            </biz-button-icon>
                        </div>
                        <div class="column is-8">
                            <biz-button-icon
                                class="is-fullwidth"
                                type="button"
                                :icon="icon.remove"
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
    import BizButtonIcon from  '@/Biz/ButtonIcon';
    import BizLink from '@/Biz/Link';
    import BizTable from '@/Biz/Table';
    import BizTag from '@/Biz/Tag';
    import Layout from '@/Layouts/User';
    import icon from '@/Libs/icon-class';

    export default {
        components: {
            BizButtonIcon,
            BizLink,
            BizTable,
            BizTag,
        },

        layout: Layout,

        props: {
            baseRouteName: { type: String, required: true },
            description: { type: String, required: true },
            order: { type: Object, required: true },
        },

        setup(props) {
            return {
                icon,
            };
        },

        methods: {
        },
    };
</script>
