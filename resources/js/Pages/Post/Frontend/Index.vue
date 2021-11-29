<template>
    <div
        id="main-container"
        class="container mt-4"
    >
        <Head>
            <title>Blog</title>
            <meta
                head-key="description"
                name="description"
                content="Blog"
            >
        </Head>

        <div class="columns">
            <div class="column">
                <sdb-filter-search
                    v-model="term"
                    @search="search"
                />
            </div>
        </div>

        <div class="table-container">
            <component
                :is="'SdbPostList'"
                :records="records.data"
            >
                <template #default="{record}">
                    <component
                        :is="'SdbPostListItem'"
                        :edit-link="route('blog.show', {slug: record.slug})"
                        :is-delete-enabled="false"
                        :is-edit-enabled="false"
                        :record="record"
                    />
                </template>
            </component>
        </div>
        <sdb-pagination
            :links="records.links"
            :query-params="queryParams"
        />
    </div>
</template>

<script>
    import PageLayout from '@/Layouts/PageLayout';
    import MixinFilterDataHandle from '@/Mixins/FilterDataHandle';
    import SdbFilterSearch from '@/Sdb/Filter/Search';
    import SdbPagination from '@/Sdb/Pagination';
    import SdbPostList from '@/Sdb/Post/List';
    import SdbPostListItem from '@/Sdb/Post/ListItem';
    import { Head } from '@inertiajs/inertia-vue3';
    import { merge, identity, pickBy } from 'lodash';
    import { ref } from 'vue';

    export default {
        components: {
            Head,
            SdbFilterSearch,
            SdbPagination,
            SdbPostList,
            SdbPostListItem,
        },
        mixins: [
            MixinFilterDataHandle,
        ],
        layout: PageLayout,
        props: {
            baseRouteName: {
                type: String,
                required: true,
            },
            categoryId: {
                type: Number,
                default: null,
            },
            currentLanguage: String,
            errors: Object,
            pageNumber: String,
            pageQueryParams: Object,
            records: {},
        },
        setup(props) {
            const queryParams = merge({id: props.categoryId}, props.pageQueryParams);

            return {
                queryParams: ref(queryParams),
                term: ref(props.pageQueryParams?.term ?? null),
            };
        },
        data() {
            return {
                loader: null,
            };
        },
        methods: {
            search(term) {
                this.queryParams['term'] = term;
                this.$inertia.get(
                    route(
                        this.baseRouteName+'.index',
                        pickBy(this.queryParams, identity)
                    ),
                    {},
                    {
                        replace: true,
                        preserveState: true,
                        onStart: () => this.onStartLoadingOverlay(),
                        onFinish: () => this.onEndLoadingOverlay(),
                    }
                );
            },
            onStartLoadingOverlay() {
                this.loader = this.$loading.show();
            },
            onEndLoadingOverlay() {
                this.loader.hide();
            },
        },
    };
</script>
