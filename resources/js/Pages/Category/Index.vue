<template>
    <app-layout>
        <template #header>Category</template>

        <div class="box">
            <div class="columns">
                <div class="column is-offset-10">
                    <div
                        v-if="can.add"
                        class="is-pulled-right"
                    >
                        <sdb-button-link :href="route(baseRoute+'.create')" class="is-primary">
                            <span class="icon is-small">
                                <i class="fas fa-plus"></i>
                            </span>
                            <span>Add New</span>
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
                <sdb-table class="is-striped is-hoverable is-fullwidth">
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
                                    <sdb-button-link
                                        v-if="can.edit"
                                        class="is-ghost has-text-black"
                                        :href="route(baseRoute + '.edit', record.id)"
                                    >
                                        <span class="icon is-small">
                                            <i class="fas fa-pen"></i>
                                        </span>
                                    </sdb-button-link>
                                    <sdb-button
                                        v-if="can.delete"
                                        class="is-ghost has-text-black ml-1"
                                        @click.prevent="deleteRow(record)"
                                    >
                                        <span class="icon is-small">
                                            <i class="far fa-trash-alt"></i>
                                        </span>
                                    </sdb-button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </sdb-table>
            </div>
            <sdb-pagination :links="records.links"></sdb-pagination>
        </div>
    </app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import SdbButton from '@/Sdb/Button';
    import SdbButtonIcon from '@/Sdb/ButtonIcon';
    import SdbButtonLink from '@/Sdb/ButtonLink';
    import SdbFormFieldHorizontal from '@/Sdb/Form/FieldHorizontal';
    import SdbInput from '@/Sdb/Input';
    import SdbPagination from '@/Sdb/Pagination';
    import SdbTable from '@/Sdb/Table';
    import { confirmDelete } from '@/Libs/alert';
    import { merge } from 'lodash';
    import { ref } from 'vue';
    import { usePage } from '@inertiajs/inertia-vue3';

    export default {
        components: {
            AppLayout,
            SdbButton,
            SdbButtonIcon,
            SdbButtonLink,
            SdbFormFieldHorizontal,
            SdbInput,
            SdbPagination,
            SdbTable,
        },
        props: {
            can: {},
            pageNumber: String,
            pageQueryParams: Object,
            records: Object,
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
                baseRoute: 'admin.categories',
                loader: null,
            };
        },
        methods: {
            deleteRow(record) {
                const self = this;
                confirmDelete().then((result) => {
                    if (result.isConfirmed) {
                        self.$inertia.delete(
                            route(self.baseRoute+'.destroy', record.id)
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
            search(term) {
                this.queryParams['term'] = term;
                this.$inertia.get(
                    route(this.baseRoute+'.index'),
                    this.queryParams,
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
