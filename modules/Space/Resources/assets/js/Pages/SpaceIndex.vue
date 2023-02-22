<template>
    <div>
        <div class="box">
            <div class="columns">
                <div class="column">
                    <biz-filter-search
                        v-model="term"
                        @search="search"
                    />
                </div>

                <div class="column">
                    <biz-dropdown
                        :close-on-click="false"
                    >
                        <template #trigger>
                            <span>
                                {{ selectedParent ? selectedParent.value : 'Select Parent' }}
                            </span>

                            <biz-icon :icon="icon.angleDown" />
                        </template>

                        <biz-dropdown-scroll
                            :max-height="300"
                        >
                            <biz-dropdown-item
                                v-for="option in parentOptions"
                                :key="option.id"
                                @click="parentId = option.id"
                            >
                                {{ option.value }}
                            </biz-dropdown-item>
                        </biz-dropdown-scroll>
                    </biz-dropdown>

                    <biz-dropdown
                        class="ml-2"
                        :close-on-click="false"
                    >
                        <template #trigger>
                            <span>Type</span>
                            <span
                                class="ml-1"
                            >
                                ({{ types.length }})
                            </span>
                            <biz-icon :icon="icon.angleDown" />
                        </template>

                        <biz-dropdown-scroll
                            :max-height="300"
                        >
                            <biz-dropdown-item
                                v-for="typeOption in typeOptions"
                                :key="typeOption.id"
                            >
                                <biz-checkbox
                                    v-model:checked="types"
                                    :value="typeOption.id"
                                    @change="onTypesChanged"
                                >
                                    &nbsp; {{ typeOption.value }}
                                </biz-checkbox>
                            </biz-dropdown-item>
                        </biz-dropdown-scroll>
                    </biz-dropdown>
                </div>

                <div class="column has-text-right">
                    <biz-button-link
                        v-if="can.add"
                        class="is-primary"
                        :href="route('admin.spaces.create')"
                    >
                        <biz-icon :icon="icon.add" />
                        <span>Create New</span>
                    </biz-button-link>
                </div>
            </div>

            <biz-table-index
                :records="records"
                :query-params="queryParams"
            >
                <template #thead>
                    <tr>
                        <th>Name</th>
                        <th>Parents</th>
                        <th>Type</th>
                        <th>
                            <div class="level-right">
                                Actions
                            </div>
                        </th>
                    </tr>
                </template>

                <space-row
                    v-for="space in records.data"
                    :key="space.id"
                    :space="space"
                    :can="can"
                    @delete-row="deleteSpace($event)"
                />
            </biz-table-index>
        </div>
    </div>
</template>

<script>
    import MixinFilterDataHandle from '@/Mixins/FilterDataHandle';
    import MixinHasLoader from '@/Mixins/HasLoader';
    import AppLayout from '@/Layouts/AppLayout.vue';
    import BizButtonLink from '@/Biz/ButtonLink.vue';
    import BizCheckbox from '@/Biz/Checkbox.vue';
    import BizDropdown from '@/Biz/Dropdown.vue';
    import BizDropdownItem from '@/Biz/DropdownItem.vue';
    import BizDropdownScroll from '@/Biz/DropdownScroll.vue';
    import BizFilterSearch from '@/Biz/Filter/Search.vue';
    import BizIcon from '@/Biz/Icon.vue';
    import BizTableIndex from '@/Biz/TableIndex.vue';
    import SpaceRow from './SpaceRow.vue';
    import icon from '@/Libs/icon-class';
    import { confirmDelete, oops as oopsAlert, success as successAlert } from '@/Libs/alert';
    import { find } from 'lodash';
    import { ref } from 'vue';

    export default {
        components: {
            BizButtonLink,
            BizCheckbox,
            BizDropdown,
            BizDropdownItem,
            BizDropdownScroll,
            BizFilterSearch,
            BizIcon,
            BizTableIndex,
            SpaceRow,
        },

        mixins: [
            MixinFilterDataHandle,
        ],

        layout: AppLayout,

        props: {
            baseRouteName: { type: String, required: true },
            can: { type: Object, required: true },
            pageQueryParams: { type: Object, default: () => {} },
            parent: { type: String, default: null },
            parentOptions: { type: Object, required: true },
            records: { type: Object, required: true },
            title: { type: String, default: "" },
            typeOptions: { type: Object, required: true },
        },

        setup(props) {
            return {
                icon,
                parentId: ref(props.parent),
                queryParams: ref(props.pageQueryParams),
                term: ref(props.pageQueryParams?.term ?? null),
                types: ref([]),
            };
        },

        computed: {
            selectedParent() {
                const parent = find(this.parentOptions, (option) => option.id == this.parentId);

                if (!parent) {
                    return this.parentOptions[0];
                }

                return parent;
            },
        },

        watch: {
            parentId(newParentId) {
                const url = route('admin.spaces.index', {parent: newParentId});

                this.$inertia.visit(url);
            },
        },

        methods: {
            deleteSpace(space) {
                const self = this;

                confirmDelete().then(result => {
                    if (result.isConfirmed) {
                        self.$inertia.delete(
                            route(this.baseRouteName+'.destroy', space.id),
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

            onTypesChanged() {
                this.queryParams['types'] = this.types;
                this.refreshWithQueryParams(); // on mixin MixinFilterDataHandle
            },
        },
    };
</script>
