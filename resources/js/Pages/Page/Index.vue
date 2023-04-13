<template>
    <div>
        <div class="box">
            <div class="columns">
                <div class="column is-4">
                    <biz-filter-search
                        v-model="term"
                        :placeholder="i18n.search"
                        @search="search"
                    />
                </div>

                <div class="column is-4 is-offset-4 has-text-right">
                    <biz-button-link
                        v-if="can.add"
                        :href="route('admin.pages.create')"
                        class="is-primary"
                    >
                        <span class="icon is-small">
                            <i :class="icon.add" />
                        </span>
                        <span>
                            {{ i18n.create_new }}
                        </span>
                    </biz-button-link>
                </div>
            </div>

            <biz-table-index
                :records="records"
                :query-params="queryParams"
            >
                <template #thead>
                    <tr>
                        <th>#</th>
                        <th>{{ i18n.title }}</th>
                        <th>{{ i18n.slug }}</th>
                        <th>{{ i18n.status }}</th>
                        <th>{{ capitalCase(i18n.meta_title) }}</th>
                        <th>{{ capitalCase(i18n.meta_description) }}</th>
                        <th>{{ i18n.language }}</th>
                        <th>
                            <div class="level-right">
                                {{ i18n.actions }}
                            </div>
                        </th>
                    </tr>
                </template>

                <tr
                    v-for="page in records.data"
                    :key="page.id"
                >
                    <th>{{ page.id }}</th>
                    <td>
                        <a
                            :href="page.urlDefaultLocale"
                            target="_blank"
                        >
                            {{ page.title }}
                        </a>
                    </td>
                    <td>{{ page.slug }}</td>
                    <td>
                        <biz-tag :class="statusClass(page.status)">
                            {{ page.statusText }}
                        </biz-tag>
                    </td>
                    <td>
                        <i
                            v-if="page.hasMetaTitle"
                            :class="icon.checkCircle"
                        />
                    </td>
                    <td>
                        <i
                            v-if="page.hasMetaDescription"
                            :class="icon.checkCircle"
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
                            <biz-button
                                v-if="can.add"
                                class="is-ghost has-text-black"
                                @click="duplicateRow(page)"
                            >
                                <span class="icon is-small">
                                    <i :class="icon.copy" />
                                </span>
                            </biz-button>

                            <biz-button-link
                                v-if="can.edit"
                                class="is-ghost has-text-black"
                                :href="route('admin.pages.edit', {id: page.id})"
                            >
                                <span class="icon is-small">
                                    <i :class="icon.edit" />
                                </span>
                            </biz-button-link>

                            <biz-button
                                v-if="can.delete"
                                class="is-ghost has-text-black ml-1"
                                @click.prevent="deleteRow(page)"
                            >
                                <span class="icon is-small">
                                    <i :class="icon.remove" />
                                </span>
                            </biz-button>
                        </div>
                    </td>
                </tr>
            </biz-table-index>
        </div>
    </div>
</template>

<script>
    import MixinFilterDataHandle from '@/Mixins/FilterDataHandle';
    import AppLayout from '@/Layouts/AppLayout.vue';
    import BizButton from '@/Biz/Button.vue';
    import BizButtonLink from '@/Biz/ButtonLink.vue';
    import BizFilterSearch from '@/Biz/Filter/Search.vue';
    import BizTableIndex from '@/Biz/TableIndex.vue';
    import BizTag from '@/Biz/Tag.vue';
    import icon from '@/Libs/icon-class';
    import { confirmDelete, confirm, success as successAlert } from '@/Libs/alert';
    import { merge, filter } from 'lodash';
    import { ref } from 'vue';
    import { capitalCase } from 'change-case';

    export default {
        name: 'PageIndex',

        components: {
            BizButton,
            BizButtonLink,
            BizFilterSearch,
            BizTableIndex,
            BizTag,
        },

        mixins: [
            MixinFilterDataHandle,
        ],

        layout: AppLayout,

        props: {
            baseRouteName: { type: String, required: true },
            can: { type: Object, required: true },
            defaultLocale: { type: String, required: true },
            i18n: {
                type: Object,
                default: () => ({
                    search : 'Search',
                    create_new : 'Create new',
                    title : 'Title',
                    slug : 'Slug',
                    status : 'Status',
                    meta_title : 'Meta title',
                    meta_description : 'Meta description',
                    language : 'Language',
                    actions : 'Actions',
                    are_you_sure : 'Are you sure?',
                    remove_page_from_navigation_message : 'This action will also remove the page on the navigation menu.',
                    yes : 'Yes',
                    duplicate_page : 'Duplicate page',
                    duplicate_page_message : 'Are you sure want to duplicate this page?',
                })
            },
            pageQueryParams: { type: Object, default: () => {} },
            records: { type: Object, required: true },
            title: { type: String, required: true },
        },

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

        data() {
            return {
                icon,
            };
        },

        methods: {
            openShow(locale, page) {
                if (this.can.read) {
                    let showUrl = this.getShowUrl(locale, page);

                    window.open(showUrl, "_blank");
                }
            },

            statusClass(status) {
                let statusClass = ['is-small', 'is-rounded'];
                switch(status) {
                    case 1 :statusClass.push('is-success'); break;
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
            },

            isUsedByMenu(pageId) {
                const self = this;

                return new Promise((resolve, reject) => {
                    const url = route('admin.api.pages.is-used-by-menu', {
                        page: pageId,
                    });

                    axios.get(url)
                        .then(response => {
                            resolve(response.data == true);
                        })
                        .catch(error => {
                            reject(error);
                        })
                });
            },

            async canDeletePage(pageId) {
                const self = this;

                try {
                    const isUsedByMenu = await self.isUsedByMenu(pageId);

                    if (isUsedByMenu) {
                        const confirmResult = await confirmDelete(
                            self.i18n.are_you_sure,
                            self.i18n.remove_page_from_navigation_message,
                            self.i18n.yes,
                        );

                        return !!confirmResult.value;
                    }

                    return await confirmDelete(
                        self.i18n.are_you_sure,
                        null,
                        self.i18n.yes,
                    ).then(result => {
                        return result.isConfirmed;
                    });

                } catch (error) {
                    console.error(error);

                    return true;
                }
            },

            async deleteRow(page) {
                const self = this;

                if (await self.canDeletePage(page.id)) {
                    const deleteRoute = route('admin.pages.destroy', {id: page.id});

                    self.$inertia.delete(
                        deleteRoute,
                        {
                            onStart: () => this.onStartLoadingOverlay(),
                            onFinish: () => this.onEndLoadingOverlay(),
                            onSuccess: self.onSuccess,
                        }
                    );
                }
            },

            onSuccess(page) {
                successAlert(page.props.flash.message);
            },

            duplicateRow(page) {
                const self = this;

                confirm(
                    self.i18n.duplicate_page,
                    self.i18n.duplicate_page_message,
                    self.i18n.yes,
                    {
                        icon: 'warning',
                    }
                )
                    .then(result => {
                        if (result.isConfirmed) {
                            self.$inertia.post(
                                route('admin.pages.duplicate', {id: page.id})
                            );
                        }
                    });
            },

            capitalCase,
        },
    }
</script>
