<template>
    <div>
        <div class="table-container">
            <biz-table class="is-fullwidth">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Date</th>
                        <th></th>
                    </tr>
                </thead>

                <tbody>
                    <tr
                        v-for="event in record.data"
                        :key="event.id"
                    >
                        <td>{{ event.title }}</td>
                        <td>{{ event.started_at }} - {{ event.ended_at }}</td>
                        <td>&nbsp;</td>
                    </tr>
                </tbody>
            </biz-table>
        </div>

        <biz-pagination
            :is-ajax="true"
            :links="events.links"
            :query-params="queryParams"
            @on-clicked-pagination="getRecords"
        />

        <space-event-form-modal
            v-if="isModalOpen"
            :selected-event="selectedEvent"
            :space="space"
            @close="closeModal()"
            @after-submit="afterSubmit"
        />
    </div>
</template>

<script>
    export default {
        props: {
            eventID: { type: String, required: true }
        },

        methods: {

        },
    };
</script>
