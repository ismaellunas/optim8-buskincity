<template>
<app-layout>
    <template v-slot:header>{{ title }}</template>

    <div class="box">
        <div class="columns">
            <div class="column">
                <div class="is-pulled-left">
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
                        <th>Name</th>
                        <th>Email</th>
                        <th>
                            <div class="level-right">Actions</div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="record in records.data" :key="record.id">
                        <th>{{ record.id }}</th>
                        <td>{{ record.name }}</td>
                        <td>{{ record.email }}</td>
                        <td>
                            <div class="level-right">
                                <sdb-button-link
                                    v-if="can.edit"
                                    class="is-ghost has-text-black"
                                    :href="route(baseRouteName + '.edit', record.id)"
                                >
                                    <span class="icon is-small">
                                        <i class="fas fa-pen"></i>
                                    </span>
                                </sdb-button-link>
                                <sdb-button
                                    v-if="can.delete && record.can.delete_user"
                                    class="is-ghost has-text-black ml-1"
                                    @click.prevent="deleteRecord(record)"
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

        <sdb-pagination
            :links="records.links"
            :query-params="queryParams"
        />

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
    import { confirmDelete, oops as oopsAlert, success as successAlert } from '@/Libs/alert';
    import { merge } from 'lodash';
    import { ref } from 'vue';

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
            baseRouteName: String,
            can: Object,
            pageNumber: String,
            pageQueryParams: Object,
            records: {},
            title: String,
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
                            {
                                onStart: self.onStartLoadingOverlay,
                                onFinish: self.onEndLoadingOverlay,
                                onError: self.onError,
                                onSuccess: self.onSuccess,
                            }
                        );
                    }
                })
            },
            onError(errors) {
                oopsAlert();
            },
            onSuccess(page) {
                successAlert(page.props.flash.message);
            },
            onStartLoadingOverlay() {
                this.loader = this.$loading.show();
            },
            onEndLoadingOverlay() {
                this.loader.hide();
            },
            search(term) {
                this.queryParams['term'] = term;
                this.$inertia.get(
                    route(this.baseRouteName+'.index', this.queryParams),
                    {},
                    {
                        replace: true,
                        preserveState: true,
                        onStart: this.onStartLoadingOverlay,
                        onFinish: this.onEndLoadingOverlay,
                    }
                );
            },
        },
    };
</script>
