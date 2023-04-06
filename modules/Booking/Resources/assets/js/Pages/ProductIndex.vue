<template>
    <div class="box">
        <div class="columns">
            <div class="column">
                <biz-filter-search
                    v-model="term"
                    :placeholder="i18n.search"
                    @search="search"
                />
            </div>

            <div class="column">
                <biz-dropdown :close-on-click="false">
                    <template #trigger>
                        <span>{{ i18n.filter }}</span>

                        <biz-icon :icon="icon.angleDown" />
                    </template>

                    <biz-dropdown-item>
                        {{ i18n.status }}
                    </biz-dropdown-item>

                    <biz-dropdown-item
                        v-for="status in statusOptions"
                        :key="status.id"
                    >
                        <biz-checkbox
                            v-model:checked="statuses"
                            :value="status.id"
                            @change="onStatusChanged"
                        >
                            &nbsp; {{ status.value }}
                        </biz-checkbox>
                    </biz-dropdown-item>
                </biz-dropdown>
            </div>

            <div class="column has-text-right">
                <biz-button-link
                    v-if="can.add"
                    :href="route(baseRouteName+'.create')"
                    class="is-primary"
                >
                    <biz-icon :icon="icon.add" />
                    <span>{{ i18n.create_new }}</span>
                </biz-button-link>
            </div>
        </div>

        <biz-table-index
            :records="products"
            :query-params="queryParams"
        >
            <template #thead>
                <tr>
                    <th>#</th>
                    <th>{{ i18n.name }}</th>
                    <th>{{ i18n.status }}</th>
                    <th>
                        <div class="level-right">
                            {{ i18n.actions }}
                        </div>
                    </th>
                </tr>
            </template>

            <tr
                v-for="product in products.data"
                :key="product.id"
            >
                <td>{{ product.id }}</td>
                <td>{{ product.name }}</td>
                <td>
                    <biz-tag
                        class="is-small is-rounded"
                        :class="{'is-primary': product.status == 'Published'}"
                    >
                        {{ product.status }}
                    </biz-tag>
                </td>
                <td>
                    <div class="level-right">
                        <biz-button-link
                            v-if="product.can.edit"
                            class="is-ghost has-text-black"
                            :href="route(baseRouteName+'.edit', product.id)"
                        >
                            <biz-icon
                                class="is-small"
                                :icon="icon.edit"
                            />
                        </biz-button-link>

                        <biz-button-icon
                            v-if="product.can.delete"
                            class="is-ghost has-text-black ml-1"
                            type="button"
                            :icon="icon.remove"
                            @click.prevent="deleteProduct(product)"
                        />
                    </div>
                </td>
            </tr>
        </biz-table-index>
    </div>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout.vue';
    import BizButtonIcon from '@/Biz/ButtonIcon.vue';
    import BizButtonLink from '@/Biz/ButtonLink.vue';
    import BizCheckbox from '@/Biz/Checkbox.vue';
    import BizDropdown from '@/Biz/Dropdown.vue';
    import BizDropdownItem from '@/Biz/DropdownItem.vue';
    import BizFilterSearch from '@/Biz/Filter/Search.vue';
    import BizIcon from '@/Biz/Icon.vue';
    import BizTableIndex from '@/Biz/TableIndex.vue';
    import BizTag from '@/Biz/Tag.vue';
    import MixinFilterDataHandle from '@/Mixins/FilterDataHandle';
    import icon from '@/Libs/icon-class';
    import { confirmDelete, oops as oopsAlert, success as successAlert } from '@/Libs/alert';
    import { merge } from 'lodash';
    import { ref } from "vue";

    export default {
        components: {
            BizButtonIcon,
            BizButtonLink,
            BizCheckbox,
            BizDropdown,
            BizDropdownItem,
            BizFilterSearch,
            BizIcon,
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
            pageQueryParams: { type: Object, default: () => {} },
            products: { type: Object, required: true },
            statusOptions: { type: Array, required: true },
            i18n: { type: Object, default: () => ({
                search : 'Search',
                filter : 'Filter',
                status : 'Status',
                create_new : 'Create New',
                name : 'Name',
                status : 'Status',
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
                statuses: ref(props.pageQueryParams?.status ?? []),
                icon,
                queryParams: ref(queryParams),
                term: ref(props.pageQueryParams?.term ?? null),
            };
        },

        methods: {
            deleteProduct(product) {
                const self = this;

                confirmDelete(
                    self.i18n.are_you_sure
                ).then(result => {
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

            onStatusChanged() {
                this.queryParams['status'] = this.statuses;
                this.refreshWithQueryParams(); // on mixin MixinFilterDataHandle
            },
        },
    };
</script>
