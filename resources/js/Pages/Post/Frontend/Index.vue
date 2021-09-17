<template>
    <Head>
        <title>Blog</title>
        <meta head-key="description" name="description" content="Blog" />
    </Head>

    <div
        id="main-container"
        class="container mt-4"
    >
        <div class="columns">
            <div class="column">
                <sdb-form-field-horizontal>
                    <template v-slot:label>
                        Search
                    </template>
                    <div class="columns">
                        <div class="column is-three-quarters">
                            <sdb-input
                                v-model="term"
                                maxlength="255"
                                @keyup.enter.prevent="search(term)"
                            />
                        </div>
                        <div class="column">
                            <sdb-button-icon
                                icon="fas fa-search"
                                type="button"
                                @click="search(term)"
                            />
                        </div>
                    </div>
                </sdb-form-field-horizontal>
            </div>
        </div>

        <div class="table-container">
            <component
                :is="'SdbPostList'"
                :records="records.data"
            >
                <template v-slot:default="{record}">
                    <component
                        :is="'SdbPostListItem'"
                        :edit-link="route(baseRouteName+'.show', {locale: currentLanguage, slug: record.slug})"
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
    import SdbButtonIcon from '@/Sdb/ButtonIcon';
    import SdbButtonLink from '@/Sdb/ButtonLink';
    import SdbFormFieldHorizontal from '@/Sdb/Form/FieldHorizontal';
    import SdbInput from '@/Sdb/Input';
    import SdbPagination from '@/Sdb/Pagination';
    import SdbPostList from '@/Sdb/Post/List';
    import SdbPostListItem from '@/Sdb/Post/ListItem';
    import { Head } from '@inertiajs/inertia-vue3';
    import { merge, identity, pickBy } from 'lodash';
    import { confirmDelete } from '@/Libs/alert';
    import { ref } from 'vue';

    export default {
        layout: PageLayout,
        components: {
            Head,
            SdbButtonIcon,
            SdbButtonLink,
            SdbFormFieldHorizontal,
            SdbInput,
            SdbPagination,
            SdbPostList,
            SdbPostListItem,
        },
        props: {
            currentLanguage: String,
            errors: Object,
            pageNumber: String,
            pageQueryParams: Object,
            records: {},
        },
        setup(props) {
            const queryParams = merge({}, props.pageQueryParams);

            return {
                queryParams: ref(queryParams),
                term: ref(props.pageQueryParams?.term ?? null),
            };
        },
        data() {
            return {
                baseRouteName: 'blog',
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
