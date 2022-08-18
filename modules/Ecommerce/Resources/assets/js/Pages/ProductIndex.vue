<template>
    <div>
        <div class="columns">
            <!--
            <div class="column">
                <div class="is-pulled-left">
                    <biz-filter-search
                        v-model="term"
                        @search="search"
                    />
                </div>
            </div>
            -->
            <div class="column">
                <div
                    v-if="can.add"
                    class="is-pulled-right"
                >
                    <biz-button-link
                        :href="route(baseRouteName+'.create')"
                        class="is-primary"
                    >
                        <biz-icon
                            class="is-small"
                            :icon="icon.add"
                        />

                        <span>Create New</span>
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
                            <div class="level-right">
                                Actions
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="product in products.data"
                        :key="product.id"
                    >
                        <td>{{ product.id }}</td>
                        <td>{{ product.name }}</td>
                        <td>
                            <div class="level-right">
                                <biz-button-link
                                    v-if="can.edit"
                                    class="is-ghost has-text-black"
                                    :href="route(baseRouteName+'.edit', product.id)"
                                >
                                    <biz-icon
                                        class="is-small"
                                        :icon="icon.edit"
                                    />
                                </biz-button-link>

                                <biz-button-icon
                                    v-if="can.delete"
                                    class="is-ghost has-text-black ml-1"
                                    type="button"
                                    :icon="icon.remove"
                                    @click.prevent="deleteProduct(product)"
                                />
                            </div>
                        </td>
                    </tr>
                </tbody>
            </biz-table>
        </div>

        <biz-pagination
            :links="products.links"
            :query-params="queryParams"
        />
    </div>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import BizButtonIcon from '@/Biz/ButtonIcon';
    import BizButtonLink from '@/Biz/ButtonLink';
    import BizIcon from '@/Biz/Icon';
    import BizPagination from '@/Biz/Pagination';
    import BizTable from '@/Biz/Table';
    import icon from '@/Libs/icon-class';
    import { confirmDelete, oops as oopsAlert, success as successAlert } from '@/Libs/alert';
    import { merge } from 'lodash';
    import { ref } from "vue";

    export default {
        components: {
            BizButtonIcon,
            BizButtonLink,
            BizIcon,
            BizPagination,
            BizTable,
        },

        layout: AppLayout,

        props: {
            baseRouteName: { type: String, required: true },
            can: { type: Object, required: true },
            pageQueryParams: { type: Array, default: () => [] },
            products: { type: Object, required: true },
        },

        setup(props) {
            const queryParams = merge(
                {},
                props.pageQueryParams
            );

            return {
                icon,
                queryParams: ref(queryParams),
                term: ref(props.pageQueryParams?.term ?? null),
            };
        },

        methods: {
            deleteProduct(product) {
                const self = this;

                confirmDelete().then(result => {
                    if (result.isConfirmed) {
                        self.$inertia.delete(
                            route(this.baseRouteName+'.destroy', product.id),
                            {
                                onStart: self.onStartLoadingOverlay,
                                onFinish: self.onEndLoadingOverlay,
                                onError: () => {
                                    oopsAlert();
                                },
                                onSuccess: (page) => {
                                    successAlert(page.props.flash.message);
                                },
                            }
                        );
                    }
                })

            },
        },
    };
</script>
