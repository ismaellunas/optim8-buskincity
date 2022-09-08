<template>
    <div>
        <div class="box">
            <div class="columns">
                <div class="column">
                    <div class="is-pulled-left">
                        <biz-filter-search
                            v-model="term"
                            @search="search"
                        />
                    </div>
                </div>
            </div>

            <div class="table-container">
                <table class="table is-striped is-hoverable is-fullwidth">
                    <thead>
                        <tr>
                            <th
                                v-for="(label, index) in fieldLabels"
                                :key="index"
                            >
                                {{ label }}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(entry, index) in records.data"
                            :key="index"
                        >
                            <td
                                v-for="(name, nameIndex) in fieldNames"
                                :key="nameIndex"
                            >
                                {{ entry[name] }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <biz-pagination
                :links="records.links"
                :query-params="queryParams"
            />
        </div>
    </div>
</template>

<script>
    import MixinFilterDataHandle from '@/Mixins/FilterDataHandle';
    import AppLayout from '@/Layouts/AppLayout';
    import BizFilterSearch from '@/Biz/Filter/Search';
    import BizPagination from '@/Biz/Pagination';
    import icon from '@/Libs/icon-class';
    import { merge } from 'lodash';
    import { ref } from 'vue';

    export default {
        name: 'FormBuilderEntries',

        components: {
            BizFilterSearch,
            BizPagination,
        },

        mixins: [
            MixinFilterDataHandle,
        ],

        layout: AppLayout,

        props: {
            baseRouteName: { type: String, required: true },
            fieldLabels: { type: Object, default: () => {} },
            fieldNames: { type: Object, default: () => {} },
            formBuilder: { type: Object, required: true },
            pageQueryParams: { type: Object, default: () => {} },
            records: { type: Object, default: () => {} },
        },

        setup(props) {
            return {
                queryParams: ref(merge({},props.pageQueryParams)),
                term: ref(props.pageQueryParams?.term ?? null),
            };
        },

        data() {
            return {
                icon
            };
        },

        methods: {
            refreshWithQueryParams() {
                this.$inertia.get(
                    route(this.baseRouteName+'.entries', this.formBuilder.id),
                    this.queryParams,
                    {
                        replace: true,
                        preserveState: true,
                        onStart: () => this.onStartLoadingOverlay(),
                        onFinish: () => this.onEndLoadingOverlay(),
                    }
                );
            },
        },
    };
</script>