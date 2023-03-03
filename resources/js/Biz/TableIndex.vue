<template>
    <div>
        <div class="table-container">
            <biz-table-info
                class="mb-2"
                :records="records"
            />

            <biz-table
                :class="tableClass"
                is-fullwidth
            >
                <thead v-if="hasTableHeadSlot">
                    <slot name="thead" />
                </thead>

                <tbody>
                    <slot />
                </tbody>

                <tfoot v-if="hasTableFootSlot">
                    <slot name="tfoot" />
                </tfoot>
            </biz-table>
        </div>

        <biz-pagination
            :is-ajax="isAjaxPagination"
            :links="records.links"
            :query-params="queryParams"
            :last-page="records.last_page ?? null"
            :current-page="records.current_page ?? null"
            @on-clicked-pagination="onClickedPagination"
        />
    </div>
</template>

<script>
    import BizPagination from '@/Biz/Pagination.vue';
    import BizTable from '@/Biz/Table.vue';
    import BizTableInfo from '@/Biz/TableInfo.vue';

    export default {
        name: 'BizTableIndex',

        components: {
            BizPagination,
            BizTable,
            BizTableInfo,
        },

        props: {
            isAjaxPagination: { type: Boolean, default: false },
            queryParams: { type: Object, default: () => {} },
            records: { type: Object, required: true },
            tableClass: { type: [Object, String, Array], default: 'is-striped is-hoverable' },
        },

        emits: [
            'on-clicked-pagination'
        ],

        computed: {
            hasTableHeadSlot() {
                return !!this.$slots.thead
            },

            hasTableFootSlot() {
                return !!this.$slots.tfoot
            },
        },

        methods: {
            onClickedPagination(url) {
                this.$emit('on-clicked-pagination', url);
            },
        },
    }
</script>