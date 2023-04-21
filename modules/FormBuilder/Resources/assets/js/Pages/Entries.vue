<template>
    <div class="box">
        <biz-tab class="is-boxed">
            <ul>
                <template
                    v-for="(tab, index) in tabs"
                    :key="index"
                >
                    <biz-tab-list
                        v-if="tab.available"
                        :is-active="isTabActive(index)"
                    >
                        <biz-link
                            :href="route('admin.form-builders.entries.index', {tab: index, form_builder: formBuilder.id})"
                        >
                            {{ tab.title }}
                        </biz-link>
                    </biz-tab-list>
                </template>
            </ul>
        </biz-tab>

        <div class="columns">
            <div class="column is-4">
                <biz-filter-search
                    v-model="term"
                    :placeholder="i18n.search"
                    @search="search"
                />
            </div>

            <div
                v-if="!isArchivedTab"
                class="column"
            >
                <biz-dropdown :close-on-click="true">
                    <template #trigger>
                        <span>{{ i18n.filter }}</span>
                        <biz-icon :icon="icon.angleDown" />
                    </template>

                    <biz-dropdown-item
                        v-for="readOption in readOptions"
                        :key="readOption.id"
                        class="p-0"
                    >
                        <biz-button
                            type="button"
                            class="is-white is-fullwidth"
                            @click.prevent="onReadOptionChanged(readOption)"
                        >
                            {{ readOption.value }}
                        </biz-button>
                    </biz-dropdown-item>
                </biz-dropdown>
            </div>
        </div>

        <biz-dropdown
            v-if="can.markAsRead || can.markAsUnread || can.archive || can.restore || can.forceDelete"
            class="mb-4"
            :close-on-click="true"
        >
            <template #trigger>
                <span>{{ i18n.actions }}</span>
                <biz-icon :icon="icon.angleDown" />
            </template>

            <template v-if="!isArchivedTab">
                <biz-dropdown-item
                    v-if="can.markAsRead"
                    class="px-0"
                >
                    <biz-button
                        class="is-white is-fullwidth"
                        type="button"
                        :disabled="!canBulkMarkAsRead"
                        @click="bulkMarkAsRead()"
                    >
                        {{ i18n.mark_as_read }}
                    </biz-button>
                </biz-dropdown-item>

                <biz-dropdown-item
                    v-if="can.markAsUnread"
                    class="px-0"
                >
                    <biz-button
                        class="is-white is-fullwidth"
                        type="button"
                        :disabled="!canBulkMarkAsUnread"
                        @click="bulkMarkAsUnread()"
                    >
                        {{ i18n.mark_as_unread }}
                    </biz-button>
                </biz-dropdown-item>

                <biz-dropdown-item
                    v-if="can.archive"
                    class="px-0"
                >
                    <biz-button
                        class="is-white is-fullwidth"
                        type="button"
                        :disabled="!canBulkArchive"
                        @click="bulkArchive()"
                    >
                        {{ i18n.archive }}
                    </biz-button>
                </biz-dropdown-item>
            </template>

            <template v-else>
                <biz-dropdown-item
                    v-if="can.restore"
                    class="px-0"
                >
                    <biz-button
                        class="is-white is-fullwidth"
                        type="button"
                        :disabled="!canBulkRestore"
                        @click="bulkRestore()"
                    >
                        {{ i18n.restore }}
                    </biz-button>
                </biz-dropdown-item>
                <biz-dropdown-item
                    v-if="can.restore"
                    class="px-0"
                >
                    <biz-button
                        class="is-white is-fullwidth"
                        type="button"
                        :disabled="!canBulkForceDelete"
                        @click="bulkForceDelete()"
                    >
                        {{ i18n.delete }}
                    </biz-button>
                </biz-dropdown-item>
            </template>
        </biz-dropdown>

        <biz-table-index
            :records="records"
            :query-params="queryParams"
        >
            <template #thead>
                <tr>
                    <th>
                        <biz-checkbox-toggle
                            v-model="isAll"
                            @click="selectAllToggle"
                        />
                    </th>
                    <th
                        v-for="(label, index) in fieldLabels"
                        :key="index"
                    >
                        {{ label }}
                    </th>
                    <th>{{ i18n.actions }}</th>
                </tr>
            </template>

            <tr
                v-for="(entry, index) in records.data"
                :key="index"
                :class="{'has-text-weight-semibold': hasUnreadIndicator(entry)}"
            >
                <td>
                    <biz-checkbox
                        v-model:checked="rawSelectedEntries"
                        :value="entry.id"
                    />
                </td>
                <td
                    v-for="(name, nameIndex) in fieldNames"
                    :key="nameIndex"
                    v-html="entry[name]"
                />
                <td class="has-text-weight-normal">
                    <div class="buttons">
                        <biz-button-link
                            class="is-ghost has-text-black"
                            title="View"
                            :href="route(baseRouteName + '.show', {form_builder: formBuilder.id, form_entry: entry.id})"
                        >
                            <span class="icon is-small">
                                <i :class="icon.eye" />
                            </span>
                        </biz-button-link>

                        <biz-dropdown
                            class="is-right"
                            :class="{'is-up': index > records.data.length - 3}"
                        >
                            <template #trigger>
                                <biz-icon
                                    class="is-small"
                                    :icon="icon.ellipsis"
                                />
                            </template>

                            <biz-dropdown-item
                                v-if="entry.can.mark_as_read"
                                tag="a"
                                @click.prevent="markAsRead(entry)"
                            >
                                {{ i18n.mark_as_read }}
                            </biz-dropdown-item>

                            <biz-dropdown-item
                                v-if="entry.can.mark_as_unread"
                                tag="a"
                                @click.prevent="markAsUnread(entry)"
                            >
                                {{ i18n.mark_as_unread }}
                            </biz-dropdown-item>

                            <biz-dropdown-item
                                v-if="entry.can.archive"
                                tag="a"
                                @click.prevent="archive(entry)"
                            >
                                {{ i18n.archive }}
                            </biz-dropdown-item>

                            <biz-dropdown-item
                                v-if="entry.can.restore"
                                tag="a"
                                @click.prevent="restore(entry)"
                            >
                                {{ i18n.restore }}
                            </biz-dropdown-item>

                            <biz-dropdown-item
                                v-if="entry.can.force_delete"
                                tag="a"
                                @click.prevent="forceDelete(entry)"
                            >
                                {{ i18n.delete }}
                            </biz-dropdown-item>
                        </biz-dropdown>
                    </div>
                </td>
            </tr>

            <template v-if="isDataEmpty && records.current_page == 1">
                <tr>
                    <td
                        colspan="99"
                        class="has-text-centered"
                    >
                        {{ i18n.data_is_empty }}
                    </td>
                </tr>
            </template>
        </biz-table-index>
    </div>
</template>

<script>
    import MixinFilterDataHandle from '@/Mixins/FilterDataHandle';
    import MixinHasLoader from '@/Mixins/HasLoader';
    import MixinHasTab from '@/Mixins/HasTab';
    import AppLayout from '@/Layouts/AppLayout.vue';
    import BizButton from '@/Biz/Button.vue';
    import BizButtonLink from '@/Biz/ButtonLink.vue';
    import BizCheckbox from '@/Biz/Checkbox.vue';
    import BizCheckboxToggle from '@/Biz/CheckboxToggle.vue';
    import BizDropdown from '@/Biz/Dropdown.vue';
    import BizDropdownItem from '@/Biz/DropdownItem.vue';
    import BizFilterSearch from '@/Biz/Filter/Search.vue';
    import BizIcon from '@/Biz/Icon.vue';
    import BizLink from '@/Biz/Link.vue';
    import BizTab from '@/Biz/Tab.vue';
    import BizTabList from '@/Biz/TabList.vue';
    import BizTableIndex from '@/Biz/TableIndex.vue';
    import icon from '@/Libs/icon-class';
    import { confirm as confirmAlert, oops as oopsAlert, success as successAlert, confirmDelete } from '@/Libs/alert';
    import { isEmpty } from 'lodash';
    import { ref, computed } from 'vue';

    export default {
        name: 'FormBuilderEntries',

        components: {
            BizButton,
            BizButtonLink,
            BizCheckbox,
            BizCheckboxToggle,
            BizDropdown,
            BizDropdownItem,
            BizFilterSearch,
            BizIcon,
            BizLink,
            BizTab,
            BizTabList,
            BizTableIndex,
        },

        mixins: [
            MixinFilterDataHandle,
            MixinHasLoader,
            MixinHasTab,
        ],

        layout: AppLayout,

        props: {
            can: { type: Object, default: () => {} },
            baseRouteName: { type: String, required: true },
            fieldLabels: { type: Object, default: () => {} },
            fieldNames: { type: Object, default: () => {} },
            formBuilder: { type: Object, required: true },
            pageQueryParams: { type: Object, default: () => {} },
            records: { type: Object, default: () => {} },
            readOptions: { type: Array, default: () => [] },
            i18n: { type: Object, default: () => ({
                entries: 'Entries',
                archived: 'Archived',
                search: 'Search',
                filter: 'Filter',
                actions: 'actions',
                mark_as_read: 'Mark as read',
                mark_as_unread: 'Mark as unread',
                archive: 'Archive',
                restore: 'Restore',
                delete: 'Delete',
                data_is_empty: 'Data is empty',
                confirm_archive: 'Confirm archive',
                are_you_sure: 'Are you sure?',
                confirm_restore: 'Confirm restore',
                confirm_deletion: 'Confirm deletion',
                confirm_deletion_message: 'Once the resources are deleted, they will be permanently deleted.',
            }) },
        },

        setup(props) {
            const tabs = {
                entries: {
                    title: props.i18n.entries,
                    available: true,
                },
                archived: {
                    title: props.i18n.archived,
                    available: props.can.restore,
                },
            };
            const filterParams = ref({});

            return {
                activeTab: ref(props.pageQueryParams?.tab ?? 'entries'),
                icon,
                isAll: ref(false),
                filterParams,
                queryParams: computed(() => {
                    return _.pickBy({
                        ...props.pageQueryParams,
                        ...filterParams.value,
                    }, (param) => !_.isNull(param));
                }),
                queryReads: ref([]),
                rawSelectedEntries: ref([]),
                term: ref(props.pageQueryParams?.term ?? null),
                tabs,
            };
        },

        computed: {
            isDataEmpty() {
                return isEmpty(this.records.data);
            },
            isArchivedTab() {
                return this.activeTab == 'archived';
            },
            canBulkMarkAsRead() {
                const self = this;
                return (
                    !this.isArchivedTab
                    && this.rawSelectedEntries.length > 0
                    && _.findIndex(this.rawSelectedEntries, function (entry) {
                        return !(_.find(self.records.data, {id: entry})?.isRead);
                    }) >= 0
                );
            },
            canBulkMarkAsUnread() {
                const self = this;
                return (
                    !this.isArchivedTab
                    && this.rawSelectedEntries.length > 0
                    && _.findIndex(this.rawSelectedEntries, function (entry) {
                        return _.find(self.records.data, {id: entry})?.isRead;
                    }) >= 0
                );
            },
            canBulkArchive() {
                return (
                    this.rawSelectedEntries.length > 0
                    && !this.isArchivedTab
                );
            },
            canBulkRestore() {
                return (
                    this.rawSelectedEntries.length > 0
                    && this.isArchivedTab
                );
            },
            canBulkForceDelete() {
                return this.canBulkRestore;
            },
        },

        methods: {
            hasUnreadIndicator(entry) {
                return (
                    !this.isArchivedTab
                    && !entry.isRead
                );
            },
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
                this.filterParams['read'] = option.id;
                this.refreshWithQueryParams(); // on mixin MixinFilterDataHandle
            },

            selectAllToggle() {
                if (!this.isAll) {
                    this.rawSelectedEntries = this.records.data.map((entry) => entry.id);
                    this.isAll = true;
                } else {
                    this.rawSelectedEntries = [];
                    this.isAll = false;
                }
            },

            bulkActionRequest(routeName, afterSuccess) {
                const self = this;
                this.$inertia.post(
                    route(routeName, {
                        ...{ form_builder: this.formBuilder.id },
                        ...this.queryParams,
                    }),
                    { entries: this.rawSelectedEntries },
                    {
                        onStart: () => this.onStartLoadingOverlay(),
                        onFinish: (visit) => {
                            self.onEndLoadingOverlay();
                        },
                        onError: (errors) => {
                            oopsAlert();
                        },
                        onSuccess: (page) => {
                            successAlert(page.props.flash.message);

                            if (_.isFunction(afterSuccess)) {
                                afterSuccess(page);
                            }
                        },
                    }
                );
            },

            actionRequest(routeName, entry, afterSuccess) {
                const self = this;

                this.$inertia.post(
                    route(routeName, {
                        ...{ form_builder: this.formBuilder.id },
                        ...this.queryParams,
                    }),
                    { entries: [entry.id] },
                    {
                        onStart: () => this.onStartLoadingOverlay(),
                        onFinish: (visit) => {
                            self.onEndLoadingOverlay();
                        },
                        onError: (errors) => {
                            oopsAlert();
                        },
                        onSuccess: (page) => {
                            successAlert(page.props.flash.message);

                            if (_.isFunction(afterSuccess)) {
                                afterSuccess(page);
                            }
                        },
                    }
                );
            },

            bulkMarkAsRead() {
                this.bulkActionRequest(this.baseRouteName + '.bulk-mark-as-read');
            },

            bulkMarkAsUnread() {
                this.bulkActionRequest(this.baseRouteName + '.bulk-mark-as-unread');
            },

            bulkArchive() {
                const self = this;

                confirmDelete(
                    self.i18n.confirm_archive,
                    self.i18n.are_you_sure
                ).then(result => {
                    if (result.isConfirmed) {
                        this.bulkActionRequest(
                            this.baseRouteName + '.bulk-archive',
                            function () {
                                self.rawSelectedEntries = [];
                            }
                        );
                    }
                });
            },

            bulkRestore() {
                const self = this;

                confirmDelete(
                    self.i18n.confirm_restore,
                    self.i18n.are_you_sure
                ).then(result => {
                    if (result.isConfirmed) {
                        this.bulkActionRequest(
                            this.baseRouteName + '.bulk-restore',
                            function () {
                                self.rawSelectedEntries = [];
                            }
                        );
                    }
                });
            },

            bulkForceDelete() {
                const self = this;

                confirmDelete(
                    self.i18n.are_you_sure,
                    self.i18n.confirm_deletion_message,
                    self.i18n.confirm_deletion
                ).then(result => {
                    if (result.isConfirmed) {
                        this.bulkActionRequest(
                            this.baseRouteName + '.bulk-force-delete',
                            function () {
                                self.rawSelectedEntries = [];
                            }
                        );
                    }
                });
            },

            markAsRead(entry) {
                this.actionRequest(this.baseRouteName + '.bulk-mark-as-read', entry);
            },

            markAsUnread(entry) {
                this.actionRequest(this.baseRouteName + '.bulk-mark-as-unread', entry);
            },

            archive(entry) {
                confirmDelete(
                    this.i18n.confirm_archive,
                    this.i18n.are_you_sure
                ).then(result => {
                    if (result.isConfirmed) {
                        this.actionRequest(this.baseRouteName + '.bulk-archive', entry);
                    }
                });
            },

            restore(entry) {
                confirmDelete(
                    this.i18n.confirm_restore,
                    this.i18n.are_you_sure
                ).then(result => {
                    if (result.isConfirmed) {
                        this.actionRequest(this.baseRouteName + '.bulk-restore', entry);
                    }
                });
            },

            forceDelete(entry) {
                confirmDelete(
                    this.i18n.are_you_sure,
                    this.i18n.confirm_deletion_message,
                    this.i18n.confirm_deletion
                ).then(result => {
                    if (result.isConfirmed) {
                        this.actionRequest(this.baseRouteName + '.bulk-force-delete', entry);
                    }
                });
            },
        },
    };
</script>