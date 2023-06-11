<template>
    <div class="column is-6-desktop is-6-tablet is-12-mobile">
        <h2 class="title is-4">
            {{ title }}
        </h2>

        <div class="box is-shadowless">
            <div class="columns is-multiline is-mobile">
                <div class="column is-12-desktop is-12-tablet is-12-mobile">
                    <biz-table
                        v-if="data.records.length > 0"
                        is-fullwidth
                    >
                        <tr
                            v-for="record in data.records"
                            :key="record.order_id"
                        >
                            <td>{{ record.name }}</td>
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
                    </biz-table>
                    <p
                        v-else
                        class="has-text-centered"
                    >
                        Empty
                    </p>
                </div>
                <div class="column is-12-desktop is-12-tablet is-12-mobile">
                    <p class="has-text-right">
                        <biz-button-link
                            class="is-small is-light"
                            :href="route('booking.orders.index')"
                        >
                            <span>More ...</span>
                        </biz-button-link>
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import BizButtonLink from '@/Biz/ButtonLink.vue';
    import BizIcon from '@/Biz/Icon.vue';
    import BizTable from '@/Biz/Table.vue';
    import icon from '@/Libs/icon-class';

    export default {
        name: 'WidgetEventUpcoming',

        components: {
            BizButtonLink,
            BizIcon,
            BizTable,
        },

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
        },
    };
</script>
