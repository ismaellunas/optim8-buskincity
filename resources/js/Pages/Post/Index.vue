<template>
    <div>
        <div class="box">
            <div class="columns">
                <div class="column">
                    <biz-filter-search
                        v-model="term"
                        :placeholder="i18n.search"
                        @search="search"
                    />
                </div>

                <div class="column">
                    <biz-dropdown
                        :close-on-click="false"
                    >
                        <template #trigger>
                            <span>
                                {{ i18n.filter }}
                            </span>
                            <span
                                v-if="filterCounter"
                                class="ml-1"
                            >
                                ({{ filterCounter }})
                            </span>
                            <biz-icon :icon="icon.angleDown" />
                        </template>

                        <biz-dropdown-item>
                            {{ i18n.category }}
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
                            {{ i18n.language }}
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

                <div class="column has-text-right">
                    <biz-button-link
                        v-if="can.add"
                        class="is-primary"
                        :href="route(baseRouteName+'.create')"
                    >
                        <biz-icon :icon="icon.add" />
                        <span>
                            {{ i18n.create_new }}
                        </span>
                    </biz-button-link>
                </div>
            </div>

            <div class="is-clearfix" />

            <div class="table-container">
                <biz-tab class="is-boxed">
                    <ul>
                        <biz-tab-list
                            v-for="tab, index in tabs"
                            :id="tab.id"
                            :key="index"
                            :is-active="isTabActive(index)"
                        >
                            <a @click.prevent="setActiveTab(index)">
                                {{ tab.title }}
                            </a>
                        </biz-tab-list>
                    </ul>
                </biz-tab>

                <div class="columns">
                    <div class="column is-4 is-offset-8">
                        <biz-buttons-display-view
                            v-model="view"
                            wrapper-tag="div"
                            class="is-right"
                            @on-view-changed="onViewChanged"
                        />
                    </div>
                </div>

                <biz-table-info
                    class="mb-2"
                    :records="records"
                />

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
    </div>
</template>

<script>
    import MixinFilterDataHandle from '@/Mixins/FilterDataHandle';
    import MixinHasTab from '@/Mixins/HasTab';
    import AppLayout from '@/Layouts/AppLayout.vue';
    import BizButtonLink from '@/Biz/ButtonLink.vue';
    import BizButtonsDisplayView from '@/Biz/ButtonsDisplayView.vue';
    import BizCheckbox from '@/Biz/Checkbox.vue';
    import BizDropdown from '@/Biz/Dropdown.vue';
    import BizDropdownItem from '@/Biz/DropdownItem.vue';
    import BizDropdownScroll from '@/Biz/DropdownScroll.vue';
    import BizFilterSearch from '@/Biz/Filter/Search.vue';
    import BizIcon from '@/Biz/Icon.vue';
    import BizPagination from '@/Biz/Pagination.vue';
    import BizPostGallery from '@/Biz/Post/Gallery.vue';
    import BizPostGalleryItem from '@/Biz/Post/GalleryItem.vue';
    import BizPostList from '@/Biz/Post/List.vue';
    import BizPostListItem from '@/Biz/Post/ListItem.vue';
    import BizTab from '@/Biz/Tab.vue';
    import BizTabList from '@/Biz/TabList.vue';
    import BizTableInfo from '@/Biz/TableInfo.vue';
    import icon from '@/Libs/icon-class';
    import { confirmDelete, success as successAlert } from '@/Libs/alert';
    import { clone, keys, head, merge } from 'lodash';
    import { ref } from 'vue';
    import { usePage } from '@inertiajs/vue3';

    export default {
        name: 'PageIndex',

        components: {
            BizButtonLink,
            BizButtonsDisplayView,
            BizCheckbox,
            BizDropdown,
            BizDropdownItem,
            BizDropdownScroll,
            BizFilterSearch,
            BizIcon,
            BizPagination,
            BizPostGallery,
            BizPostGalleryItem,
            BizPostList,
            BizPostListItem,
            BizTab,
            BizTabList,
            BizTableInfo,
        },

        mixins: [
            MixinFilterDataHandle,
            MixinHasTab,
        ],

        layout: AppLayout,

        props: {
            can: { type: Object, required: true },
            categoryOptions: { type: Object, default: () => {} },
            languageOptions: { type: Object, default: () => {} },
            pageNumber: { type: String, default: null },
            pageQueryParams: { type: Object, default: () => {} },
            records: { type: Object, required: true },
            title: { type: String, required: true },
            i18n: { type: Object, default: () => ({
                search : 'Search',
                create_new : 'Create new',
                published : 'Published',
                scheduled : 'Scheduled',
                draft : 'Draft',
                filter : 'Filter',
                category : 'Category',
                language : 'Language',
                are_you_sure : 'Are you sure?',
            }) }
        },

        setup(props) {
            const queryParams = merge(
                {view: 'gallery'},
                props.pageQueryParams
            );

            return {
                categories: ref(props.pageQueryParams?.categories ?? []),
                icon,
                languages: ref(props.pageQueryParams?.languages ?? []),
                localeOptions: ref(usePage().props.languageOptions ?? []),
                queryParams: ref(queryParams),
                term: ref(props.pageQueryParams?.term ?? null),
                view: ref(props.pageQueryParams?.view ?? 'gallery'),
                tabs: {
                    published: { title: props.i18n.published, id: 'published-tab-trigger' },
                    scheduled: { title: props.i18n.scheduled, id: 'scheduled-tab-trigger' },
                    draft: { title: props.i18n.draft, id: 'draft-tab-trigger' },
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

                confirmDelete(self.i18n.are_you_sure).then(result => {
                    if (result.isConfirmed) {
                        self.$inertia.delete(
                            route(self.baseRouteName+'.destroy', record.id),
                            {
                                onStart: () => this.onStartLoadingOverlay(),
                                onFinish: () => this.onEndLoadingOverlay(),
                                onSuccess: self.onSuccess,
                            }
                        );
                    }
                })
            },

            onSuccess(page) {
                successAlert(page.props.flash.message);
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
