<template>
    <biz-table is-fullwidth>
        <tr>
            <th><biz-icon :icon="icon.calendar" /></th>
            <td>{{ event.start_end_time }} / {{ event.date }}</td>
        </tr>
        <tr>
            <th><biz-icon :icon="icon.timezone" /></th>
            <td>{{ event.display_timezone }} ({{ event.timezoneOffset }})</td>
        </tr>
        <tr>
            <th><biz-icon :icon="icon.duration" /></th>
            <td>{{ event.duration }}</td>
        </tr>

        <template v-if="location">
            <tr>
                <th><biz-icon :icon="icon.locationMark" /></th>
                <td>
                    <span>{{ location.address }}</span>
                    <span>, {{ location.city }}</span>
                    <span>, {{ location.country_name }}</span>
                    <div class="buttons my-2">
                        <a
                            v-if="location.direction_url"
                            class="button is-link my-0"
                            target="_blank"
                            :href="location.direction_url"
                        >
                            Directions
                        </a>
                    </div>
                </td>
            </tr>
        </template>

        <tr v-if="userName">
            <th><biz-icon :icon="icon.user" /></th>
            <td>{{ userName }}</td>
        </tr>

        <tr v-if="product.is_check_in_required">
            <th><biz-icon :icon="icon.buildingCheck" /></th>
            <td>
                <span v-if="checkInTime">
                    {{ checkInTime }}
                </span>
                <span v-else>
                    Not yet checked in
                </span>
            </td>
        </tr>

        <slot />
    </biz-table>
</template>

<script>
    import BizIcon from '@/Biz/Icon.vue';
    import BizTable from '@/Biz/Table.vue';
    import { buildingCheck, calendar, duration, locationMark, timezone, user } from '@/Libs/icon-class';

    export default {
        name: 'TableEventDetail',

        components: {
            BizIcon,
            BizTable,
        },

        props: {
            checkInTime: { type: [String, null], default: null },
            event: { type: Object, required: true },
            isFullwidth: { type: Boolean, default: true },
            location: { type: Object, default: () => {}  },
            product: { type: Object, required: true },
            userName: { type: String, default: "" },
        },

        setup(props) {
            return {
                icon: { buildingCheck, calendar, duration, locationMark, timezone, user },
            };
        },
    };
</script>
