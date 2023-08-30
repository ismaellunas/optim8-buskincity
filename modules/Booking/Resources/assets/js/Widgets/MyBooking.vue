<template>
    <div
        class="column"
        :class="columnClasses"
    >
        <h2 class="title is-4">
            {{ startCase(title) }}
        </h2>

        <div class="box is-shadowless">
            <div class="columns is-multiline is-mobile">
                <div class="column is-12-desktop is-12-tablet is-12-mobile">
                    <biz-table
                        v-if="data.records.length > 0"
                        is-fullwidth
                    >
                        <thead>
                            <tr>
                                <th>{{ i18n.product }}</th>
                                <th>{{ i18n.city }}</th>
                                <th>{{ i18n.country }}</th>
                                <th>{{ i18n.time }}</th>
                                <th>{{ i18n.actions }}</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr
                                v-for="record in data.records"
                                :key="record.order_id"
                            >
                                <td>{{ record.name }}</td>
                                <td>{{ record.city }}</td>
                                <td>{{ record.country }}</td>
                                <td>{{ record.booked_at }}</td>
                                <td>
                                    <biz-button-link
                                        class="is-small is-link is-light"
                                        :href="record.url"
                                    >
                                        <biz-icon :icon="icon.show" />
                                    </biz-button-link>
                                </td>
                            </tr>
                        </tbody>
                    </biz-table>
                    <p
                        v-else
                        class="has-text-centered"
                    >
                        {{ i18n.empty }}
                    </p>
                </div>
                <div class="column is-12-desktop is-12-tablet is-12-mobile">
                    <p class="has-text-right">
                        <biz-button-link
                            class="is-small is-light"
                            :href="route('booking.orders.index')"
                        >
                            <span>{{ i18n.button_more }} ...</span>
                        </biz-button-link>
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import MixinWidget from '@/Mixins/Widget';
    import BizButtonLink from '@/Biz/ButtonLink.vue';
    import BizIcon from '@/Biz/Icon.vue';
    import BizTable from '@/Biz/Table.vue';
    import icon from '@/Libs/icon-class';
    import { startCase } from 'lodash';

    export default {
        name: 'WidgetEventUpcoming',

        components: {
            BizButtonLink,
            BizIcon,
            BizTable,
        },

        mixins: [
            MixinWidget,
        ],

        props: {
            data: {type: Object, required: true},
            title: {type: String, default: ""},
        },

        setup() {
            return {
                icon,
            };
        },

        methods: {
            startCase,
        },
    };
</script>
