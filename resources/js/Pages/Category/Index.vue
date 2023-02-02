<template>
    <div>
        <div class="box">
            <div class="columns">
                <div class="column is-4">
                    <biz-filter-search
                        v-model="term"
                        @search="search"
                    />
                </div>

                <div class="column is-4 is-offset-4 has-text-right">
                    <biz-button-link
                        v-if="can.add"
                        class="is-primary"
                        :href="route(baseRouteName+'.create')"
                    >
                        <biz-icon :icon="icon.add" />
                        <span>Create New</span>
                    </biz-button-link>
                </div>
            </div>

            <div class="table-container">
                <biz-table-info :records="records" />

                <biz-table class="is-striped is-hoverable is-fullwidth">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th
                                v-for="locale in localeOptions"
                                :key="locale.id"
                            >
                                {{ locale.name }}
                            </th>
                            <th>
                                <div class="level-right">
                                    Actions
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="record in records.data"
                            :key="record.id"
                        >
                            <th>{{ record.id }}</th>
                            <td
                                v-for="locale in localeOptions"
                                :key="locale.id"
                            >
                                {{ getNameByLocale(record, locale.id) }}
                            </td>
                            <td>
                                <div class="level-right">
                                    <biz-button-link
                                        v-if="can.edit"
                                        class="is-ghost has-text-black"
                                        :href="route(baseRouteName + '.edit', record.id)"
                                    >
                                        <biz-icon :icon="icon.edit" />
                                    </biz-button-link>
                                    <biz-button-icon
                                        v-if="can.delete"
                                        class="is-ghost has-text-black ml-1"
                                        :icon="icon.remove"
                                        @click.prevent="deleteRow(record)"
                                    />
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </biz-table>
            </div>
            <biz-pagination
                :links="records.links"
                :query-params="queryParams"
            />
        </div>
    </div>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import MixinFilterDataHandle from '@/Mixins/FilterDataHandle';
    import BizButtonIcon from '@/Biz/ButtonIcon';
    import BizButtonLink from '@/Biz/ButtonLink';
    import BizFilterSearch from '@/Biz/Filter/Search';
    import BizIcon from '@/Biz/Icon';
    import BizPagination from '@/Biz/Pagination';
    import BizTable from '@/Biz/Table';
    import BizTableInfo from '@/Biz/TableInfo';
    import icon from '@/Libs/icon-class';
    import { confirmDelete } from '@/Libs/alert';
    import { merge } from 'lodash';
    import { ref } from 'vue';
    import { usePage } from '@inertiajs/inertia-vue3';

    export default {
        components: {
            BizButtonIcon,
            BizButtonLink,
            BizFilterSearch,
            BizIcon,
            BizPagination,
            BizTable,
            BizTableInfo,
        },
        mixins: [
            MixinFilterDataHandle,
        ],
        layout: AppLayout,
        props: {
            baseRouteName: String,
            can: {},
            pageNumber: String,
            pageQueryParams: Object,
            records: Object,
            title: { type: String, required: true },
        },
        setup(props) {
            return {
                defaultLocale: usePage().props.value.defaultLanguage,
                localeOptions: usePage().props.value.languageOptions,
                queryParams: ref(merge({},props.pageQueryParams)),
                term: ref(props.pageQueryParams?.term ?? null),
            };
        },
        data() {
            return {
                loader: null,
                icon,
            };
        },
        methods: {
            deleteRow(record) {
                const self = this;
                confirmDelete().then((result) => {
                    if (result.isConfirmed) {
                        self.$inertia.delete(
                            route(self.baseRouteName+'.destroy', record.id)
                        );
                    }
                });
            },
            getNameByLocale(record, locale) {
                const translation = record.translations.find(translation => translation.locale === locale);
                if (translation) {
                    return translation.name;
                }
                return "";
            },
        },
    };
</script>
