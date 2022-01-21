<template>
    <app-layout>
        <template v-slot:header>{{ title }}</template>

        <div class="box">
            <div class="columns">
                <div class="column">
                    <div class="is-pulled-left">
                        <biz-filter-search
                            v-model="term"
                            @search="search"
                        ></biz-filter-search>
                    </div>
                </div>
                <div class="column">
                    <div class="is-pulled-right">
                        <biz-button-link
                            class="is-primary"
                            :href="route(baseRouteName+'.create')"
                        >
                            <span class="icon is-small">
                                <i class="fas fa-plus"></i>
                            </span>
                            <span>Add New</span>
                        </biz-button-link>
                    </div>
                </div>
            </div>

            <div class="table-container">
                <biz-table class="is-striped is-hoverable is-fullwidth">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>
                                <div class="level-right">Actions</div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="record in records.data" :key="record.id">
                            <th>{{ record.id }}</th>
                            <td>{{ record.name }}</td>
                            <td>
                                <div class="level-right">
                                    <biz-button-link
                                        class="is-ghost has-text-black"
                                        :href="route(baseRouteName + '.edit', record.id)"
                                    >
                                        <span class="icon is-small">
                                            <i class="fas fa-pen"></i>
                                        </span>
                                    </biz-button-link>
                                    <biz-button
                                        class="is-ghost has-text-black ml-1"
                                        @click.prevent="deleteRecord(record)"
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
    import { confirmDelete, oops as oopsAlert, success as successAlert } from '@/Libs/alert';
    import { merge } from 'lodash';
    import { ref } from 'vue';

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
        },
    };
</script>
