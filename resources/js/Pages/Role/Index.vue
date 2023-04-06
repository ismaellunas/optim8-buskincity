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
                        class="is-primary"
                        :href="route(baseRouteName+'.create')"
                    >
                        <biz-icon :icon="icon.add" />
                        <span>{{ i18n.create_new }}</span>
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
                        <th>{{ i18n.name }}</th>
                        <th>
                            <div class="level-right">
                                {{ i18n.actions }}
                            </div>
                        </th>
                    </tr>
                </template>

                <tr
                    v-for="record in records.data"
                    :key="record.id"
                >
                    <th>{{ record.id }}</th>
                    <td>{{ record.name }}</td>
                    <td>
                        <div class="level-right">
                            <biz-button-link
                                class="is-ghost has-text-black"
                                :href="route(baseRouteName + '.edit', record.id)"
                            >
                                <biz-icon :icon="icon.edit" />
                            </biz-button-link>
                            <biz-button-icon
                                class="is-ghost has-text-black ml-1"
                                :icon="icon.remove"
                                @click.prevent="deleteRecord(record)"
                            />
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
    import BizButtonIcon from '@/Biz/ButtonIcon.vue';
    import BizButtonLink from '@/Biz/ButtonLink.vue';
    import BizFilterSearch from '@/Biz/Filter/Search.vue';
    import BizIcon from '@/Biz/Icon.vue';
    import BizTableIndex from '@/Biz/TableIndex.vue';
    import icon from '@/Libs/icon-class';
    import { confirmDelete, oops as oopsAlert, success as successAlert } from '@/Libs/alert';
    import { merge } from 'lodash';
    import { ref } from 'vue';

    export default {
        name: 'RoleIndex',

        components: {
            BizButtonIcon,
            BizButtonLink,
            BizFilterSearch,
            BizIcon,
            BizTableIndex
        },

        mixins: [
            MixinFilterDataHandle,
        ],

        layout: AppLayout,

        props: {
            baseRouteName: { type: String, required: true },
            pageQueryParams: { type: Object, default: () => {} },
            records: { type: Object, required: true },
            title: { type: String, required: true },
            i18n: { type: Object, default: () => ({
                search : 'Search',
                create_new : 'Create New',
                name : 'Name',
                actions : 'Actions',
                are_you_sure : 'Are you sure?',
            }) },
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
                loader: null,
            };
        },

        methods: {
            deleteRecord(record) {
                const self = this;

                confirmDelete(
                    self.i18n.are_you_sure
                ).then(result => {
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
