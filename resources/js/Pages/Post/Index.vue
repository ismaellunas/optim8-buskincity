<template>
<app-layout>
    <template #header>Post</template>

    <div class="box">
        <div class="columns">
            <div class="column is-offset-10">
                <div class="is-pulled-right">
                    <sdb-button-link
                        class="is-primary"
                        :href="route(baseRouteName+'.create')"
                    >
                        <span class="icon is-small">
                            <i class="fas fa-plus"></i>
                        </span>
                        <span>Create New</span>
                    </sdb-button-link>
                </div>
            </div>
        </div>

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
            <sdb-tab>
                <ul>
                    <sdb-tab-list
                        v-for="tab, index in tabs"
                        :key="index"
                        :is-active="isTabActive(index)"
                    >
                        <a @click.prevent="setActiveTab(index)">
                            {{ tab.title }}
                        </a>
                    </sdb-tab-list>
                </ul>

                <sdb-buttons-display-view
                    v-model="view"
                    @on-view-changed="onViewChanged"
                />
            </sdb-tab>

            <component
                :is="isGalleryView ? 'SdbPostGallery' : 'SdbPostList'"
                :records="records.data"
            >
                <template v-slot:default="{record}">
                    <component
                        :is="isGalleryView ? 'SdbPostGalleryItem' : 'SdbPostListItem'"
                        :record="record"
                        :edit-link="route(baseRouteName+'.edit', record.id)"
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
</app-layout>
</template>

<script>
    import MixinHasTab from '@/Mixins/HasTab';
    import AppLayout from '@/Layouts/AppLayout';
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
        components: {
            AppLayout,
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
        mixins: [
            MixinHasTab
        ],
        props: {
            pageNumber: String,
            pageQueryParams: Object,
            records: {},
        },
        setup(props) {
            const queryParams = merge(
                {view: 'gallery'},
                props.pageQueryParams
            );

            return {
                queryParams: ref(queryParams),
                term: ref(props.pageQueryParams?.term ?? null),
                view: ref(props.pageQueryParams?.view ?? 'gallery'),
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
                baseRouteName: 'admin.posts',
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
