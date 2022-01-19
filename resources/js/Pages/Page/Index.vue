<template>
    <app-layout>
        <template #header>{{ title }}</template>

        <biz-flash-notifications :flash="$page.props.flash"/>

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
                    <div
                        v-if="can.add"
                        class="is-pulled-right"
                    >
                        <biz-button-link
                            :href="route('admin.pages.create')"
                            class="is-primary"
                        >
                            <span class="icon is-small">
                                <i class="fas fa-plus" />
                            </span>
                            <span>Create New</span>
                        </biz-button-link>
                    </div>
                </div>
            </div>

            <div class="table-container">
                <table class="table is-striped is-hoverable is-fullwidth">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Title</th>
                            <th>Slug</th>
                            <th>Status</th>
                            <th>M.Title</th>
                            <th>M.Description</th>
                            <th>Language</th>
                            <th>
                                <div class="level-right">Actions</div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="page in records.data"
                            :key="page.id"
                        >
                            <th>{{ page.id }}</th>
                            <td>{{ page.title }}</td>
                            <td>{{ page.slug }}</td>
                            <td>
                                <biz-tag :class="statusClass(page.status)">
                                    {{ page.statusText }}
                                </biz-tag>
                            </td>
                            <td>
                                <i
                                    v-if="page.hasMetaTitle"
                                    class="far fa-check-circle"
                                />
                            </td>
                            <td>
                                <i
                                    v-if="page.hasMetaDescription"
                                    class="far fa-check-circle"
                                />
                            </td>
                            <td>
                                <div class="buttons">
                                    <biz-button
                                        v-for="(translation, index) in page.availableTranslations"
                                        :key="index"
                                        class="is-info px-2 mr-1 is-small"
                                        @click="openShow(translation, page)"
                                    >
                                        {{ translation?.toUpperCase() }}
                                    </biz-button>
                                </div>
                            </td>
                            <td>
                                <div class="level-right">
                                    <biz-button-link
                                        v-if="can.edit"
                                        class="is-ghost has-text-black"
                                        :href="route('admin.pages.edit', {id: page.id})"
                                    >
                                        <span class="icon is-small">
                                            <i class="fas fa-pen" />
                                        </span>
                                    </biz-button-link>
                                    <biz-button
                                        v-if="can.delete"
                                        class="is-ghost has-text-black ml-1"
                                        @click.prevent="deleteRow(page)"
                                    >
                                        <span class="icon is-small">
                                            <i class="far fa-trash-alt" />
                                        </span>
                                    </biz-button>
                                </div>
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
    </app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import MixinFilterDataHandle from '@/Mixins/FilterDataHandle';
    import BizButton from '@/Biz/Button';
    import BizButtonLink from '@/Biz/ButtonLink';
    import BizFilterSearch from '@/Biz/Filter/Search';
    import BizFlashNotifications from '@/Biz/FlashNotifications';
    import BizPagination from '@/Biz/Pagination';
    import BizTag from '@/Biz/Tag';
    import { confirmDelete } from '@/Libs/alert';
    import { merge, filter } from 'lodash';
    import { ref } from 'vue';

    export default {
        components: {
            AppLayout,
            BizButton,
            BizButtonLink,
            BizFilterSearch,
            BizFlashNotifications,
            BizPagination,
            BizTag,
        },
        mixins: [
            MixinFilterDataHandle,
        ],
        props: [
            'baseRouteName',
            'can',
            'defaultLocale',
            'pageQueryParams',
            'records',
            'title',
        ],
        setup(props) {
            const queryParams = merge(
                {},
                props.pageQueryParams
            );

            return {
                queryParams: ref(queryParams),
                term: ref(props.pageQueryParams?.term ?? null),
            };
        },
        methods: {
            deleteRow(page) {
                const self = this;

                confirmDelete().then(result => {
                    if (result.isConfirmed) {
                        self.$inertia.delete(route('admin.pages.destroy', {id: page.id}));
                    }
                });
            },
            openShow(locale, page) {
                if (this.can.read) {
                    let showUrl = this.getShowUrl(locale, page);

                    window.open(showUrl, "_blank");
                }
            },
            statusClass(status) {
                let statusClass = ['is-small', 'is-rounded'];
                switch(status) {
                    case 1 : statusClass.push('is-success'); break;
                    default: statusClass.push('is-light');
                };
                return statusClass;
            },

            getShowUrl(locale, page) {
                let translation = filter(page.translations, { 'locale': locale });
                let showUrl = null;
                let defaultUrl = route('frontend.pages.show', {
                    page_translation: translation.length ? translation[0].slug : page.slug
                });
                let url = new URL(defaultUrl);

                if (locale !== this.defaultLocale) {
                    showUrl = defaultUrl.replace(url.pathname, "/"+locale+url.pathname);
                } else {
                    showUrl = defaultUrl;
                }

                return showUrl;
            }
        },
    }
</script>
