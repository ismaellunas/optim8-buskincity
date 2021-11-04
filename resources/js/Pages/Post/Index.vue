<template>
<app-layout>
    <template #header>Post</template>

    <div class="box">
        <div class="columns">
            <div class="column">
                <div class="is-pulled-left">
                    <sdb-filter-search
                        v-model="term"
                        @search="search"
                    ></sdb-filter-search>
                </div>
            </div>

            <div class="column">
                <sdb-dropdown
                    :close-on-click="false"
                >
                    <template v-slot:trigger>
                        <span>Filter</span>
                        <span
                            v-if="filterCounter"
                            class="ml-1"
                        >
                            ({{ filterCounter }})
                        </span>
                        <span class="icon is-small">
                            <i class="fas fa-angle-down" aria-hidden="true"></i>
                        </span>
                    </template>

                    <sdb-dropdown-item>
                        Categories
                    </sdb-dropdown-item>

                    <sdb-dropdown-item v-for="category in categoryOptions">
                        <sdb-checkbox
                            v-model:checked="categories"
                            :value="category.id"
                            @change="onCategoriesChanged"
                        >
                            &nbsp; {{ category.value }}
                        </sdb-checkbox>
                    </sdb-dropdown-item>

                    <hr class="dropdown-divider">

                    <sdb-dropdown-item>
                        Languages
                    </sdb-dropdown-item>

                    <sdb-dropdown-item v-for="language in languageOptions">
                        <sdb-checkbox
                            v-model:checked="languages"
                            :value="language.id"
                            @change="onLanguagesChanged"
                        >
                            &nbsp; {{ language.name }}
                        </sdb-checkbox>
                    </sdb-dropdown-item>
                </sdb-dropdown>
            </div>

            <div class="column">
                <div
                    v-if="can.add"
                    class="is-pulled-right"
                >
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
                        :is-delete-enabled="can.delete"
                        :is-edit-enabled="can.edit"
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
    import MixinFilterDataHandle from '@/Mixins/FilterDataHandle';
    import MixinHasTab from '@/Mixins/HasTab';
    import AppLayout from '@/Layouts/AppLayout';
    import SdbButtonLink from '@/Sdb/ButtonLink';
    import SdbButtonsDisplayView from '@/Sdb/ButtonsDisplayView';
    import SdbCheckbox from '@/Sdb/Checkbox';
    import SdbDropdown from '@/Sdb/Dropdown';
    import SdbDropdownItem from '@/Sdb/DropdownItem';
    import SdbFilterSearch from '@/Sdb/Filter/Search';
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
    import { usePage } from '@inertiajs/inertia-vue3';

    export default {
        components: {
            AppLayout,
            SdbButtonLink,
            SdbButtonsDisplayView,
            SdbCheckbox,
            SdbDropdown,
            SdbDropdownItem,
            SdbFilterSearch,
            SdbPagination,
            SdbPostGallery,
            SdbPostGalleryItem,
            SdbPostList,
            SdbPostListItem,
            SdbTab,
            SdbTabList,
        },
        mixins: [
            MixinFilterDataHandle,
            MixinHasTab,
        ],
        props: {
            can: {},
            categoryOptions: Object,
            languageOptions: Object,
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
                categories: ref(props.pageQueryParams?.categories ?? []),
                localeOptions: ref(usePage().props.value.languageOptions ?? []),
                languages: ref(props.pageQueryParams?.languages ?? []),
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
            onLanguagesChanged() {
                this.queryParams['languages'] = this.languages;
                this.refreshWithQueryParams(); // on mixin MixinFilterDataHandle
            },
            onCategoriesChanged() {
                this.queryParams['categories'] = this.categories;
                this.refreshWithQueryParams(); // on mixin MixinFilterDataHandle
            },
        },
        computed: {
            isGalleryView() {
                return this.view === 'gallery';
            },
            filterCounter() {
                return this.categories.length + this.languages.length;
            },
        },
    };
</script>
