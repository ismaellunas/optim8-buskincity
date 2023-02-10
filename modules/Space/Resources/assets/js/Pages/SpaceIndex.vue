<template>
    <div>
        <div class="box">
            <div class="columns">
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
    import AppLayout from '@/Layouts/AppLayout';
    import MixinHasLoader from '@/Mixins/HasLoader';
    import BizButtonLink from '@/Biz/ButtonLink';
    import BizDropdown from '@/Biz/Dropdown';
    import BizDropdownItem from '@/Biz/DropdownItem';
    import BizDropdownScroll from '@/Biz/DropdownScroll';
    import BizIcon from '@/Biz/Icon';
    import BizTableIndex from '@/Biz/TableIndex';
    import SpaceRow from './SpaceRow';
    import icon from '@/Libs/icon-class';
    import { confirmDelete, oops as oopsAlert, success as successAlert } from '@/Libs/alert';
    import { find } from 'lodash';
    import { ref } from 'vue';

    export default {
        components: {
            BizButtonLink,
            BizDropdown,
            BizDropdownItem,
            BizDropdownScroll,
            BizIcon,
            BizTableIndex,
            SpaceRow,
        },

        mixins: [
            MixinHasLoader,
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
        },

        setup(props) {
            return {
                icon,
                parentId: ref(props.parent),
                queryParams: ref(props.pageQueryParams),
                term: ref(props.pageQueryParams?.term ?? null),
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
        },
    };
</script>
