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
                :is="isGalleryView ? 'SdbPostGallery' : 'SdbPostList'"
                :records="records.data"
            >
                <template v-slot:default="{record}">
                    <component
                        :is="isGalleryView ? 'SdbPostGalleryItem' : 'SdbPostListItem'"
                        :edit-link="route(baseRouteName+'.show', {locale: currentLanguage, slug: record.slug})"
                        :is-delete-enabled="false"
                        :is-edit-enabled="false"
                        :record="record"
                        @on-delete-clicked="deleteRecord"
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
    import { Head } from '@inertiajs/inertia-vue3';
    import SdbButtonIcon from '@/Sdb/ButtonIcon';
    import SdbButtonLink from '@/Sdb/ButtonLink';
    import SdbButtonsDisplayView from '@/Sdb/ButtonsDisplayView';
    import SdbFormFieldHorizontal from '@/Sdb/Form/FieldHorizontal';
    import SdbInput from '@/Sdb/Input';
    import SdbPagination from '@/Sdb/Pagination';
    import SdbPostGallery from '@/Sdb/Post/Gallery';
    import SdbPostGalleryItem from '@/Sdb/Post/GalleryItem';
    import SdbPostList from '@/Sdb/Post/List';
    import SdbPostListItem from '@/Sdb/Post/ListItem';
    import SdbTab from '@/Sdb/Tab';
    import SdbTabList from '@/Sdb/TabList';
    import { confirmDelete } from '@/Libs/alert';
    import { clone, keys, head, merge } from 'lodash';
    import { ref } from 'vue';

    export default {
        layout: PageLayout,
        components: {
            Head,
            SdbButtonIcon,
            SdbButtonLink,
            SdbButtonsDisplayView,
            SdbFormFieldHorizontal,
            SdbInput,
            SdbPagination,
            SdbPostGallery,
            SdbPostGalleryItem,
            SdbPostList,
            SdbPostListItem,
            SdbTab,
            SdbTabList,
        },
        props: {
            pageNumber: String,
            pageQueryParams: Object,
            records: {},
            currentLanguage: String,
            errors: Object,
        },
        setup(props) {
            const queryParams = merge(
                {view: 'gallery'},
                props.pageQueryParams
            );

            return {
                queryParams: ref(queryParams),
                term: ref(props.pageQueryParams?.term ?? null),
                view: ref(props.pageQueryParams?.view ?? 'list'),
                tabs: {
                    published: { title: 'Published'},
                    scheduled: {title: 'Scheduled'},
                    draft: {title: 'Draft'},
                },
            };
        },
        data() {
            return {
                activeTab: this.queryParams?.status ?? head(keys(this.tabs)),
                baseRouteName: 'blog',
                loader: null,
            };
        },
        methods: {
            deleteRecord(record) {
                const self = this;
                confirmDelete().then(result => {
                    if (result.isConfirmed) {
                        self.$inertia.delete(
                            route(self.baseRouteName+'.destroy', record.id),
                            {},
                            {
                                onStart: () => this.onStartLoadingOverlay(),
                                onFinish: () => this.onEndLoadingOverlay(),
                            }
                        );
                    }
                })
            },
            search(term) {
                this.queryParams['term'] = term;
                this.$inertia.get(
                    route(this.baseRouteName+'.index', this.queryParams),
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
            onTabSelected(tab) {
                this.queryParams['status'] = tab;
                this.$inertia.get(
                    route(this.baseRouteName+'.index', this.queryParams),
                    {},
                    {
                        only: ['records', 'pageQueryParams'],
                        onStart: () => this.onStartLoadingOverlay(),
                        onFinish: () => this.onEndLoadingOverlay(),
                    }
                );
            },
            onViewChanged(view) {
                this.queryParams['view'] = view;
                const clonedQueryParam = clone(this.queryParams);

                this.$inertia.get(
                    route(
                        this.baseRouteName+'.index',
                        merge(clonedQueryParam, {page: this.pageNumber})
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
        },
        computed: {
            isGalleryView() {
                return this.view === 'gallery';
            },
        },
    };
</script>
