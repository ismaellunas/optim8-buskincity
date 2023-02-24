<template>
    <div>
        <div class="box">
            <div class="columns">
                <div class="column">
                    <biz-form-dropdown-search
                        :close-on-click="true"
                        :is-clearable="true"
                        @search="searchUsers($event)"
                    >
                        <template #trigger>
                            <span :style="{'min-width': '4rem'}">
                                {{ selectedUser?.value ?? 'Select User' }}
                            </span>
                        </template>

                        <biz-dropdown-item
                            @click="selectedUser = null"
                        >
                            (None)
                        </biz-dropdown-item>

                        <biz-dropdown-item
                            v-for="user in users"
                            :key="user.id"
                            @click="selectedUser = user"
                        >
                            {{ user.value }}
                        </biz-dropdown-item>
                    </biz-form-dropdown-search>
                </div>
            </div>

            <div class="table-container">
                <biz-table class="is-striped is-hoverable is-fullwidth">
                    <thead>
                        <tr>
                            <th>Verb</th>
                            <th>Path</th>
                            <th>Status</th>
                            <th>User</th>
                            <th>Happened At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-if="hasNewEntries">
                            <td
                                colspan="100"
                                class="has-text-centered"
                            >
                                <biz-button
                                    v-if="!loadingNewEntries"
                                    class="is-primary is-small"
                                    type="button"
                                    @click.prevent="loadNewEntries"
                                >
                                    <span>Load New Entries</span>
                                </biz-button>

                                <biz-tag v-if="loadingNewEntries">
                                    Loading ...
                                </biz-tag>
                            </td>
                        </tr>

                        <tr
                            v-for="record in entries"
                            :key="record.id"
                        >
                            <th>
                                <biz-tag>
                                    {{ record.content.method }}
                                </biz-tag>
                            </th>
                            <td>{{ truncate(record.content.uri, {length: 50}) }}</td>
                            <td>
                                <biz-tag :class="statusCodeColor(record.content.response_status)">
                                    {{ record.content.response_status }}
                                </biz-tag>
                            </td>
                            <td>
                                <template v-if="record.content?.user">
                                    <biz-link
                                        v-if="can.user.edit"
                                        :href="route('admin.users.edit', record.content.user.id)"
                                    >
                                        {{ record.content.user?.name ?? '' }}
                                    </biz-link>

                                    <template v-else>
                                        {{ record.content.user?.name ?? '' }}
                                    </template>
                                </template>
                            </td>
                            <td>{{ record.created_at }}</td>
                            <td>
                                <biz-button-icon
                                    class="is-ghost has-text-black ml-1"
                                    icon-class="is-small"
                                    title="View"
                                    type="button"
                                    :icon="iconEye"
                                    @click.prevent="viewDetail(record)"
                                />
                            </td>
                        </tr>

                        <tr v-if="hasMoreEntries">
                            <td
                                colspan="100"
                                class="has-text-centered"
                            >
                                <biz-button
                                    v-if="!loadingMoreEntries"
                                    class="is-primary is-small"
                                    type="button"
                                    @click.prevent="loadOlderEntries"
                                >
                                    <span>Load More</span>
                                </biz-button>

                                <biz-tag v-if="loadingMoreEntries">
                                    Loading ...
                                </biz-tag>
                            </td>
                        </tr>
                    </tbody>
                </biz-table>
            </div>
        </div>

        <modal-entry-detail
            v-if="isModalOpen"
            :entry="selectedEntry"
            @close-modal="closeModal"
            @open-modal="openModal"
        />
    </div>
</template>

<script>
    import MixinHasModal from '@/Mixins/HasModal';
    import AppLayout from '@/Layouts/AppLayout.vue';
    import ModalEntryDetail from './ModalEntryDetail.vue';
    import BizButton from '@/Biz/Button.vue';
    import BizButtonIcon from '@/Biz/ButtonIcon.vue';
    import BizDropdownItem from '@/Biz/DropdownItem.vue';
    import BizFormDropdownSearch from '@/Biz/Form/DropdownSearch.vue';
    import BizLink from '@/Biz/Link.vue';
    import BizTable from '@/Biz/Table.vue';
    import BizTag from '@/Biz/Tag.vue';
    import _ from 'lodash';
    import { debounceTime } from '@/Libs/defaults';
    import { eye as iconEye } from '@/Libs/icon-class';
    import { statusCodeColor } from '@/Libs/utils';

    export default {
        name: 'SystemLog',

        components: {
            BizButton,
            BizButtonIcon,
            BizDropdownItem,
            BizFormDropdownSearch,
            BizLink,
            BizTable,
            BizTag,
            ModalEntryDetail,
        },

        mixins: [
            MixinHasModal,
        ],

        layout: AppLayout,

        props: {
            can: { type: Object, default: () => {} },
            pageQueryParams: { type: Object, required: true},
            tagAuth: { type: [Object, null], default: null },
        },

        data() {
            return {
                hasMoreEntries: true,
                hasNewEntries: false,
                lastEntryIndex: '',
                ready: false,
                recordingStatus: 'enabled',
                loadingMoreEntries: false,
                loadingNewEntries: false,
                autoLoadsNewEntries: false,

                resource: 'requests',
                basePath: '/telescope',

                entries: [],
                tag: '',
                entriesPerRequest: 50,
                familyHash: '',

                newEntriesTimeout: null,
                newEntriesTimer: 4000,

                updateEntriesTimeout: null,
                updateEntriesTimer: 4000,

                selectedEntry: null,
                iconEye,

                users: [],
                selectedUser: this.tagAuth ?? null,
            };
        },

        watch: {
            selectedUser(value, oldValue) {

                if (_.isEmpty(value)) {
                    this.tag = '';
                } else {
                    this.tag = 'Auth:' + value.id;
                }

                this.$inertia.get(
                    route('admin.system-log.index'),
                    { tag: this.tag },
                    {
                        replace: true,
                        preserveState: true,
                        onStart: () => {
                            this.hasNewEntries = false;
                            this.lastEntryIndex = '';

                            clearTimeout(this.newEntriesTimeout);
                        },
                        onFinish: () => {
                            this.loadEntries((entries) => {
                                this.entries = entries;

                                this.checkForNewEntries();

                                this.ready = true;
                            });
                        },
                    }
                );
            },
        },

        mounted() {
            this.familyHash = this.pageQueryParams.family_hash || '';

            this.tag = this.pageQueryParams.tag || '';

            this.loadEntries((entries) => {
                this.entries = entries;

                this.checkForNewEntries();

                this.ready = true;
            });

            this.updateEntries();

            this.loadUserOptions();
        },

        unmounted() {
            clearTimeout(this.newEntriesTimeout);
            clearTimeout(this.updateEntriesTimeout);

            document.onkeyup = null;
        },

        methods: {
            truncate: _.truncate,

            requestPromise(queryParams = {}, params = null) {
                const url = this.basePath + '/telescope-api/' + this.resource;

                const baseQueryParams = {
                    tag: this.tag,
                    family_hash: this.familyHash,
                };

                return axios.post(url, params, { params: {
                    ...baseQueryParams,
                    ...queryParams
                }});
            },

            loadEntries(after) {
                this.requestPromise(
                    {
                        before: this.lastEntryIndex,
                        take: this.entriesPerRequest,
                    }
                ).then(response => {
                    this.lastEntryIndex = response.data.entries.length
                        ? _.last(response.data.entries).sequence
                        : this.lastEntryIndex;

                    this.hasMoreEntries = response.data.entries.length >= this.entriesPerRequest;

                    this.recordingStatus = response.data.status;

                    if (_.isFunction(after)) {
                        after(response.data.entries);
                    }
                });
            },

            loadNewEntries(){
                this.hasMoreEntries = true;
                this.hasNewEntries = false;
                this.lastEntryIndex = '';
                this.loadingNewEntries = true;

                clearTimeout(this.newEntriesTimeout);

                this.loadEntries((entries) => {
                    this.entries = entries;

                    this.loadingNewEntries = false;

                    this.checkForNewEntries();
                });
            },

            loadOlderEntries(){
                this.loadingMoreEntries = true;

                this.loadEntries((entries) => {
                    this.entries.push(...entries);

                    this.loadingMoreEntries = false;
                });
            },

            checkForNewEntries(){
                this.newEntriesTimeout = setTimeout(() => {
                    this.requestPromise(
                        { take: 1 }
                    ).then(response => {
                        if (! this._isDestroyed) {
                            this.recordingStatus = response.data.status;

                            if (response.data.entries.length && !this.entries.length) {
                                this.loadNewEntries();
                            } else if (
                                response.data.entries.length
                                && _.first(response.data.entries).id !== _.first(this.entries).id
                            ) {
                                if (this.autoLoadsNewEntries) {
                                    this.loadNewEntries();
                                } else {
                                    this.hasNewEntries = true;
                                }
                            } else {
                                this.checkForNewEntries();
                            }
                        }
                    })
                }, this.newEntriesTimer);
            },

            updateEntries(){
                this.updateEntriesTimeout = setTimeout(() => {
                    let uuids = _.chain(this.entries)
                        .filter(entry => entry.content.status === 'pending')
                        .map('id')
                        .value();

                    if (uuids.length) {
                        axios.post(this.basePath + '/telescope-api/' + this.resource, {
                            uuids: uuids
                        }).then(response => {
                            this.recordingStatus = response.data.status;

                            this.entries = _.map(this.entries, entry => {
                                if (!_.includes(uuids, entry.id)) return entry;

                                return _.find(response.data.entries, {id: entry.id});
                            });
                        })
                    }

                    this.updateEntries();
                }, this.updateEntriesTimer);
            },

            viewDetail(entry) {
                this.selectedEntry = entry;
                this.openModal();
            },

            onCloseModal() { /* @override Mixins/HasModal */
                this.selectedEntry = null;
            },

            loadUserOptions(term) {
                axios
                    .get(route('admin.system-log.search-users'), {
                        params: { term }
                    })
                    .then((response) => {
                        this.users = response.data;
                    });
            },

            searchUsers: _.debounce(function(term) {
                this.loadUserOptions(term);
            }, debounceTime),

            statusCodeColor: statusCodeColor,
        },
    };
</script>
