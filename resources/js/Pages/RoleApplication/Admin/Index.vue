<template>
    <div>
        <div class="box">
            <div class="columns">
                <div class="column is-6">
                    <biz-filter-search
                        v-model="term"
                        :placeholder="i18n.search"
                        @search="search"
                    />
                </div>
                <div class="column is-4">
                    <biz-form-select
                        v-model="status"
                        class="is-fullwidth"
                        :label="i18n.status"
                        @change="refreshWithQueryParams"
                    >
                        <option value="">All</option>
                        <option
                            v-for="option in statusOptions"
                            :key="option.id"
                            :value="option.id"
                        >
                            {{ option.value }}
                        </option>
                    </biz-form-select>
                </div>
            </div>

            <biz-table-index
                :records="records"
                :query-params="queryParams"
            >
                <template #thead>
                    <tr>
                        <th>{{ i18n.applicant }}</th>
                        <th>{{ i18n.city }}</th>
                        <th>{{ i18n.role }}</th>
                        <th>{{ i18n.status }}</th>
                        <th>{{ i18n.submitted }}</th>
                        <th>{{ i18n.actions }}</th>
                    </tr>
                </template>
                <tr
                    v-for="record in records.data"
                    :key="record.id"
                >
                    <td>{{ record.first_name }} {{ record.last_name }}<br><small>{{ record.email }}</small></td>
                    <td>{{ record.city?.name }}</td>
                    <td>{{ record.requested_role }}</td>
                    <td>{{ record.status }}</td>
                    <td>{{ record.created_at }}</td>
                    <td>
                        <biz-button-link
                            class="is-small is-link"
                            :href="route(baseRouteName + '.show', record.id)"
                        >
                            Review
                        </biz-button-link>
                    </td>
                </tr>
            </biz-table-index>
        </div>
    </div>
</template>

<script>
    import MixinFilterDataHandle from '@/Mixins/FilterDataHandle';
    import AppLayout from '@/Layouts/AppLayout.vue';
    import BizButtonLink from '@/Biz/ButtonLink.vue';
    import BizFilterSearch from '@/Biz/Filter/Search.vue';
    import BizFormSelect from '@/Biz/Form/Select.vue';
    import BizTableIndex from '@/Biz/TableIndex.vue';
    import { ref } from 'vue';

    export default {
        components: {
            BizButtonLink,
            BizFilterSearch,
            BizFormSelect,
            BizTableIndex,
        },

        mixins: [MixinFilterDataHandle],

        layout: AppLayout,

        props: {
            baseRouteName: { type: String, required: true },
            records: { type: Object, required: true },
            pageQueryParams: { type: Object, default: () => ({}) },
            statusOptions: { type: Array, default: () => [] },
            title: { type: String, required: true },
            i18n: { type: Object, default: () => ({}) },
        },

        setup(props) {
            return {
                term: ref(props.pageQueryParams?.term ?? null),
                status: ref(props.pageQueryParams?.status ?? ''),
                queryParams: ref(props.pageQueryParams),
            };
        },
    };
</script>
