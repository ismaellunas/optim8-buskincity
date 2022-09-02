<template>
    <div class="columns">
        <div class="column is-7">
            <div class="box">
                <table class="table is-fullwidth">
                    <tr
                        v-for="(line, index) in order.lines"
                        :key="index"
                    >
                        <td>
                            <div class="columns is-multiline">
                                <div class="column is-full">
                                    <div class="mb-2">
                                        <h5 class="title is-5">
                                            {{ line.purchasable.name }}
                                        </h5>
                                    </div>
                                    <div class="tags are-medium">
                                        <span class="tag">{{ line.identifier }}</span>
                                    </div>
                                </div>

                                <div class="column is-full">
                                    <table class="table is-fullwidth">
                                        <tbody>
                                            <tr>
                                                <th>Booked at</th>
                                                <td>{{ line.scheduleBooking.booked_at }}</td>
                                            </tr>
                                            <tr>
                                                <th>Timezone</th>
                                                <td>{{ line.scheduleBooking.timezone }}</td>
                                            </tr>
                                            <tr>
                                                <th>Duration</th>
                                                <td>{{ line.scheduleBooking.duration }}</td>
                                            </tr>
                                            <tr>
                                                <th>Status</th>
                                                <td>{{ line.scheduleBooking.status }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="column is-5">
            <div class="box">
                <table class="table is-fullwidth">
                    <tr>
                        <th>Status</th>
                        <td class="has-text-right">
                            {{ order.status }}
                        </td>
                    </tr>
                    <tr>
                        <th>Reference</th>
                        <td class="has-text-right">
                            {{ order.reference }}
                        </td>
                    </tr>
                    <tr>
                        <th>User</th>
                        <td class="has-text-right">
                            {{ order.user_full_name }}
                        </td>
                    </tr>
                    <tr>
                        <th>Date Placed</th>
                        <td class="has-text-right">
                            {{ order.formatted_placed_at }}
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';

    export default {
        layout: AppLayout,

        props: {
            //can: { type: Object, required: true },
            baseRouteName: { type: String, required: true },
            order: { type: Object, required: true },
        },
    };
</script>
