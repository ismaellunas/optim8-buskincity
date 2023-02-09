<template>
    <div>
        <div class="box">
            <div class="columns">
                <div class="column">
                    <biz-button-link
                        class="is-link is-pulled-left mr-5"
                        :href="route(baseRouteName + '.index')"
                    >
                        <span class="icon-text">
                            <span class="icon">
                                <i :class="icon.back" />
                            </span>
                            <span>Back</span>
                        </span>
                    </biz-button-link>

                    <div class="is-pulled-left">
                        <biz-filter-search
                            v-model="term"
                            @search="search"
                        />
                    </div>
                </div>
            </div>

            <template
                v-if="!isDataEmpty"
            >
                <div class="table-container">
                    <biz-table-info :records="records" />

                    <table class="table is-striped is-hoverable is-fullwidth">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th
                                    v-for="(label, index) in fieldLabels"
                                    :key="index"
                                >
                                    {{ label }}
                                </th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(entry, index) in records.data"
                                :key="index"
                            >
                                <td>{{ entry.id }}</td>
                                <td
                                    v-for="(name, nameIndex) in fieldNames"
                                    :key="nameIndex"
                                    v-html="entry[name]"
                                />
                                <td>
                                    <biz-button-link
                                        class="is-ghost has-text-black"
                                        title="View"
                                        :href="route(baseRouteName + '.entries.show', {form_builder: formBuilder.id, entry: entry.id})"
                                    >
                                        <span class="icon is-small">
                                            <i :class="icon.eye" />
                                        </span>
                                    </biz-button-link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <biz-pagination
                    :links="records.links"
                    :query-params="queryParams"
                />
            </template>

            <template
                v-else
            >
                <p class="has-text-centered">
                    Data is empty
                </p>
            </template>
        </div>
    </div>
</template>

<script>
    import MixinFilterDataHandle from '@/Mixins/FilterDataHandle';
    import AppLayout from '@/Layouts/AppLayout';
    import BizButtonLink from '@/Biz/ButtonLink';
    import BizFilterSearch from '@/Biz/Filter/Search';
    import BizPagination from '@/Biz/Pagination';
    import BizTableInfo from '@/Biz/TableInfo';
    import icon from '@/Libs/icon-class';
    import { merge, isEmpty } from 'lodash';
    import { ref } from 'vue';

    export default {
        name: 'FormBuilderEntries',

        components: {
            BizButtonLink,
            BizFilterSearch,
            BizPagination,
            BizTableInfo,
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

        computed: {
            isDataEmpty() {
                return isEmpty(this.records.data);
            },
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