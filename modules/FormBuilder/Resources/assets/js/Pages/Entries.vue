<template>
    <div>
        <div class="box">
            <div class="columns">
                <div class="column is-4">
                    <biz-filter-search
                        v-model="term"
                        @search="search"
                    />
                </div>

                <div class="column">
                    <biz-dropdown :close-on-click="false">
                        <template #trigger>
                            <span>Filter</span>
                            <biz-icon :icon="icon.angleDown" />
                        </template>

                        <biz-dropdown-item
                            v-for="readOption in readOptions"
                            :key="readOption.id"
                        >
                            <biz-button
                                type="button"
                                class="is-small is-white"
                                @click.prevent="onReadOptionChanged(readOption)"
                            >
                                {{ readOption.value }}
                            </biz-button>
                        </biz-dropdown-item>
                    </biz-dropdown>
                </div>
            </div>

            <template
                v-if="!isDataEmpty"
            >
                <biz-table-index
                    :records="records"
                    :query-params="queryParams"
                >
                    <template #thead>
                        <tr>
                            <th>#</th>
                            <th
                                v-for="(label, index) in fieldLabels"
                                :key="index"
                            >
                                {{ label }}
                            </th>
                            <th>Action</th>
                        </tr>
                    </template>

                    <tr
                        v-for="(entry, index) in records.data"
                        :key="index"
                    >
                        <td>{{ entry.id }}</td>
                        <td
                            v-for="(name, nameIndex) in fieldNames"
                            :key="nameIndex"
                            v-html="entry[name]"
                        />
                        <td>
                            <biz-button-link
                                class="is-ghost has-text-black"
                                title="View"
                                :href="route(baseRouteName + '.show', {form_builder: formBuilder.id, entry: entry.id})"
                            >
                                <span class="icon is-small">
                                    <i :class="icon.eye" />
                                </span>
                            </biz-button-link>
                        </td>
                    </tr>
                </biz-table-index>
            </template>

            <template
                v-else
            >
                <p class="has-text-centered">
                    Data is empty
                </p>
            </template>
        </div>
    </div>
</template>

<script>
    import MixinFilterDataHandle from '@/Mixins/FilterDataHandle';
    import AppLayout from '@/Layouts/AppLayout.vue';
    import BizButton from '@/Biz/Button.vue';
    import BizButtonLink from '@/Biz/ButtonLink.vue';
    import BizDropdown from '@/Biz/Dropdown.vue';
    import BizDropdownItem from '@/Biz/DropdownItem.vue';
    import BizFilterSearch from '@/Biz/Filter/Search.vue';
    import BizIcon from '@/Biz/Icon.vue';
    import BizTableIndex from '@/Biz/TableIndex.vue';
    import icon from '@/Libs/icon-class';
    import { merge, isEmpty } from 'lodash';
    import { ref } from 'vue';

    export default {
        name: 'FormBuilderEntries',

        components: {
            BizButton,
            BizButtonLink,
            BizDropdown,
            BizDropdownItem,
            BizFilterSearch,
            BizIcon,
            BizTableIndex,
        },

        mixins: [
            MixinFilterDataHandle,
        ],

        layout: AppLayout,

        props: {
            baseRouteName: { type: String, required: true },
            fieldLabels: { type: Object, default: () => {} },
            fieldNames: { type: Object, default: () => {} },
            formBuilder: { type: Object, required: true },
            pageQueryParams: { type: Object, default: () => {} },
            records: { type: Object, default: () => {} },
            readOptions: { type: Array, default: () => [] },
        },

        setup(props) {
            return {
                queryParams: ref(merge({},props.pageQueryParams)),
                queryReads: ref([]),
                term: ref(props.pageQueryParams?.term ?? null),
                icon,
            };
        },

        computed: {
            isDataEmpty() {
                return isEmpty(this.records.data);
            },
        },

        methods: {
            refreshWithQueryParams() {
                this.$inertia.get(
                    route(this.baseRouteName+'.index', {form_builder: this.formBuilder.id}),
                    this.queryParams,
                    {
                        replace: true,
                        preserveState: true,
                        onStart: () => this.onStartLoadingOverlay(),
                        onFinish: () => this.onEndLoadingOverlay(),
                    }
                );
            },

            onReadOptionChanged(option) {
                this.queryParams['read'] = option.id;
                this.refreshWithQueryParams(); // on mixin MixinFilterDataHandle
            },
        },
    };
</script>