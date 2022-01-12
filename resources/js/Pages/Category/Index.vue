<template>
    <app-layout>
        <template #header>{{ title }}</template>

        <div class="box">
            <div class="columns">
                <div class="column is-offset-10">
                    <div
                        v-if="can.add"
                        class="is-pulled-right"
                    >
                        <biz-button-link :href="route(baseRouteName+'.create')" class="is-primary">
                            <span class="icon is-small">
                                <i class="fas fa-plus"></i>
                            </span>
                            <span>Add New</span>
                        </biz-button-link>
                    </div>
                </div>
            </div>

            <div class="columns">
                <div class="column">
                    <biz-filter-search
                        v-model="term"
                        @search="search"
                    ></biz-filter-search>
                </div>
            </div>

            <div class="table-container">
                <biz-table class="is-striped is-hoverable is-fullwidth">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th v-for="locale in localeOptions" :key="locale.id">
                                {{ locale.name }}
                            </th>
                            <th>
                                <div class="level-right">Actions</div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="record in records.data" :key="record.id">
                            <th>{{ record.id }}</th>
                            <td v-for="locale in localeOptions" :key="locale.id">
                                {{ getNameByLocale(record, locale.id) }}
                            </td>
                            <td>
                                <div class="level-right">
                                    <biz-button-link
                                        v-if="can.edit"
                                        class="is-ghost has-text-black"
                                        :href="route(baseRouteName + '.edit', record.id)"
                                    >
                                        <span class="icon is-small">
                                            <i class="fas fa-pen"></i>
                                        </span>
                                    </biz-button-link>
                                    <biz-button
                                        v-if="can.delete"
                                        class="is-ghost has-text-black ml-1"
                                        @click.prevent="deleteRow(record)"
                                    >
                                        <span class="icon is-small">
                                            <i class="far fa-trash-alt"></i>
                                        </span>
                                    </biz-button>
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
    </app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import MixinFilterDataHandle from '@/Mixins/FilterDataHandle';
    import BizButton from '@/Biz/Button';
    import BizButtonLink from '@/Biz/ButtonLink';
    import BizFilterSearch from '@/Biz/Filter/Search';
    import BizPagination from '@/Biz/Pagination';
    import BizTable from '@/Biz/Table';
    import { confirmDelete } from '@/Libs/alert';
    import { merge } from 'lodash';
    import { ref } from 'vue';
    import { usePage } from '@inertiajs/inertia-vue3';

    export default {
        components: {
            AppLayout,
            BizButton,
            BizButtonLink,
            BizFilterSearch,
            BizPagination,
            BizTable,
        },
        mixins: [
            MixinFilterDataHandle,
        ],
        props: {
            baseRouteName: String,
            can: {},
            pageNumber: String,
            pageQueryParams: Object,
            records: Object,
            title: String,
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
