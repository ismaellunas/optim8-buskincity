<template>
    <app-layout>
        <template #header>Category</template>

        <div class="box">
            <div class="columns">
                <div class="column is-offset-10">
                    <div class="is-pulled-right">
                        <sdb-button-link :href="route(baseRoute+'.create')" class="is-primary">
                            <span class="icon is-small">
                                <i class="fas fa-plus"></i>
                            </span>
                            <span>Add New</span>
                        </sdb-button-link>
                    </div>
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
                                        class="is-ghost has-text-black"
                                        :href="route(baseRoute + '.edit', record.id)"
                                    >
                                        <span class="icon is-small">
                                            <i class="fas fa-pen"></i>
                                        </span>
                                    </sdb-button-link>
                                    <sdb-button
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
    import SdbButtonLink from '@/Sdb/ButtonLink';
    import SdbPagination from '@/Sdb/Pagination';
    import SdbTable from '@/Sdb/Table';
    import { confirmDelete } from '@/Libs/alert';
    import { usePage } from '@inertiajs/inertia-vue3';

    export default {
        components: {
            AppLayout,
            SdbButton,
            SdbButtonLink,
            SdbPagination,
            SdbTable,
        },
        props: ['records'],
        setup() {
            return {
                defaultLocale: usePage().props.value.defaultLanguage,
                localeOptions: usePage().props.value.languageOptions,
            };
        },
        data() {
            return {
                baseRoute: 'admin.categories',
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
            }
        },
    };
</script>
