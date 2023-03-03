<template>
    <biz-table is-fullwidth>
        <tr>
            <th><biz-icon :icon="bookingIcon.calendar" /></th>
            <td>{{ event.start_end_time }} / {{ event.date }}</td>
        </tr>
        <tr>
            <th><biz-icon :icon="bookingIcon.timezone" /></th>
            <td>{{ event.timezone }} ({{ event.timezoneOffset }})</td>
        </tr>
        <tr>
            <th><biz-icon :icon="bookingIcon.duration" /></th>
            <td>{{ event.duration }}</td>
        </tr>

        <template v-if="location">
            <tr>
                <th><biz-icon :icon="iconLocationMark" /></th>
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
            <th><biz-icon :icon="iconUser" /></th>
            <td>{{ userName }}</td>
        </tr>

        <tr v-if="product.is_check_in_required">
            <th><biz-icon :icon="iconBuildingCheck" /></th>
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
    import bookingIcon from '@booking/Libs/booking-icon';
    import {
        locationMark as iconLocationMark,
        buildingCheck as iconBuildingCheck,
        user as iconUser
    } from '@/Libs/icon-class';

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
                bookingIcon,
                iconBuildingCheck,
                iconLocationMark,
                iconUser,
            };
        },
    };
</script>
