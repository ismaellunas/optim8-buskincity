<template>
    <app-layout>
        <template #header>Post</template>

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

                <div class="column">
                    <biz-dropdown
                        :close-on-click="false"
                    >
                        <template #trigger>
                            <span>Filter</span>
                            <span
                                v-if="filterCounter"
                                class="ml-1"
                            >
                                ({{ filterCounter }})
                            </span>
                            <span class="icon is-small">
                                <i
                                    class="fas fa-angle-down"
                                    aria-hidden="true"
                                />
                            </span>
                        </template>

                        <biz-dropdown-item>
                            Categories
                        </biz-dropdown-item>

                        <biz-dropdown-scroll
                            :max-height="300"
                        >
                            <biz-dropdown-item
                                v-for="category in categoryOptions"
                                :key="category.id"
                            >
                                <biz-checkbox
                                    v-model:checked="categories"
                                    :value="category.id"
                                    @change="onCategoriesChanged"
                                >
                                    &nbsp; {{ category.value }}
                                </biz-checkbox>
                            </biz-dropdown-item>
                        </biz-dropdown-scroll>

                        <hr class="dropdown-divider">

                        <biz-dropdown-item>
                            Languages
                        </biz-dropdown-item>

                        <biz-dropdown-scroll
                            :max-height="300"
                        >
                            <biz-dropdown-item
                                v-for="language in languageOptions"
                                :key="language.id"
                            >
                                <biz-checkbox
                                    v-model:checked="languages"
                                    :value="language.id"
                                    @change="onLanguagesChanged"
                                >
                                    &nbsp; {{ language.name }}
                                </biz-checkbox>
                            </biz-dropdown-item>
                        </biz-dropdown-scroll>
                    </biz-dropdown>
                </div>

                <div class="column">
                    <div
                        v-if="can.add"
                        class="is-pulled-right"
                    >
                        <biz-button-link
                            class="is-primary"
                            :href="route(baseRouteName+'.create')"
                        >
                            <span class="icon is-small">
                                <i class="fas fa-plus" />
                            </span>
                            <span>Create New</span>
                        </biz-button-link>
                    </div>
                </div>
            </div>

            <div class="columns" />

            <div class="table-container">
                <biz-tab>
                    <ul>
                        <biz-tab-list
                            v-for="tab, index in tabs"
                            :key="index"
                            :is-active="isTabActive(index)"
                        >
                            <a @click.prevent="setActiveTab(index)">
                                {{ tab.title }}
                            </a>
                        </biz-tab-list>
                    </ul>

                    <biz-buttons-display-view
                        v-model="view"
                        @on-view-changed="onViewChanged"
                    />
                </biz-tab>

                <component
                    :is="isGalleryView ? 'BizPostGallery' : 'BizPostList'"
                    :records="records.data"
                >
                    <template #default="{record}">
                        <component
                            :is="isGalleryView ? 'BizPostGalleryItem' : 'BizPostListItem'"
                            :is-delete-enabled="can.delete"
                            :is-edit-enabled="can.edit"
                            :record="record"
                            :edit-link="route(baseRouteName+'.edit', record.id)"
                            :preview-link="route('blog.show', record.slug)"
                            @on-delete-clicked="deleteRecord"
                        />
                    </template>
                </component>
            </div>
            <biz-pagination
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
    import BizButtonLink from '@/Biz/ButtonLink';
    import BizButtonsDisplayView from '@/Biz/ButtonsDisplayView';
    import BizCheckbox from '@/Biz/Checkbox';
    import BizDropdown from '@/Biz/Dropdown';
    import BizDropdownItem from '@/Biz/DropdownItem';
    import BizDropdownScroll from '@/Biz/DropdownScroll';
    import BizFilterSearch from '@/Biz/Filter/Search';
    import BizPagination from '@/Biz/Pagination';
    import BizPostGallery from '@/Biz/Post/Gallery';
    import BizPostGalleryItem from '@/Biz/Post/GalleryItem';
    import BizPostList from '@/Biz/Post/List';
    import BizPostListItem from '@/Biz/Post/ListItem';
    import BizTab from '@/Biz/Tab';
    import BizTabList from '@/Biz/TabList';
    import { confirmDelete } from '@/Libs/alert';
    import { clone, keys, head, merge } from 'lodash';
    import { ref } from 'vue';
    import { usePage } from '@inertiajs/inertia-vue3';

    export default {
        components: {
            AppLayout,
            BizButtonLink,
            BizButtonsDisplayView,
            BizCheckbox,
            BizDropdown,
            BizDropdownItem,
            BizDropdownScroll,
            BizFilterSearch,
            BizPagination,
            BizPostGallery,
            BizPostGalleryItem,
            BizPostList,
            BizPostListItem,
            BizTab,
            BizTabList,
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
        computed: {
            isGalleryView() {
                return this.view === 'gallery';
            },
            filterCounter() {
                return this.categories.length + this.languages.length;
            },
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
    };
</script>
