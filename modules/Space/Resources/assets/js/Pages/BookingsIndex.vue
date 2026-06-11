<template>
    <div class="box">
        <biz-table-index
            :records="records"
            :is-ajax-pagination="true"
            :query-params="queryParams"
            @on-clicked-pagination="getRecords"
        >
            <template #thead>
                <tr>
                    <th>{{ i18n.performer }}</th>
                    <th>{{ i18n.pitch }}</th>
                    <th>{{ i18n.start }}</th>
                    <th>{{ i18n.end }}</th>
                    <th>{{ i18n.status }}</th>
                </tr>
            </template>

            <tr
                v-for="record in records.data"
                :key="record.id"
            >
                <td>{{ record.performer }}</td>
                <td>{{ record.pitch }}</td>
                <td>{{ record.started_at }}</td>
                <td>{{ record.ended_at }}</td>
                <td>
                    <biz-tag
                        class="is-small is-rounded"
                        :class="{ 'is-success': record.status_class === 'success' }"
                    >
                        {{ record.status }}
                    </biz-tag>
                </td>
            </tr>
        </biz-table-index>
    </div>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout.vue';
    import BizTableIndex from '@/Biz/TableIndex.vue';
    import BizTag from '@/Biz/Tag.vue';

    export default {
        name: 'BookingsIndex',

        components: {
            BizTableIndex,
            BizTag,
        },

        layout: AppLayout,

        props: {
            title: { type: String, default: '' },
            i18n: { type: Object, default: () => ({
                performer: 'Performer',
                pitch: 'Pitch',
                start: 'Start',
                end: 'End',
                status: 'Status',
            }) },
        },

        data() {
            return {
                queryParams: {},
                records: {
                    data: [],
                },
            };
        },

        beforeMount() {
            this.getRecords();
        },

        methods: {
            getRecords(url = null) {
                url = url ?? route('admin.bookings.records');

                axios.get(url, {
                    params: this.queryParams,
                }).then((response) => {
                    this.records = response.data;
                });
            },
        },
    };
</script>
