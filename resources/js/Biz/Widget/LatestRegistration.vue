<template>
    <div class="column is-6">
        <biz-panel class="is-white is-relative">
            <template #heading>
                <div class="columns">
                    <div class="column">
                        {{ capitalCase(title) }}
                    </div>
                    <div class="column">
                        <biz-button-link
                            v-if="data.permissions.add"
                            class="is-primary is-small is-pulled-right"
                            :href="route(data.baseRouteName+'.create')"
                        >
                            <span class="icon is-small">
                                <i :class="icon.add" />
                            </span>
                            <span>{{ i18n.add_new }}</span>
                        </biz-button-link>
                    </div>
                </div>
            </template>

            <template #default>
                <biz-loader
                    v-model="isLoading"
                    :is-full-page="false"
                />

                <biz-panel-block>
                    <div class="field is-grouped is-grouped-multiline">
                        <div class="control">
                            <biz-input
                                v-model="search.term"
                                class="is-small"
                                :placeholder="i18n.search"
                                maxlength="255"
                                @keyup.prevent="onSearch(search.term)"
                            />
                        </div>

                        <div class="control">
                            <biz-dropdown
                                :close-on-click="false"
                                class-button="is-small"
                            >
                                <template #trigger>
                                    <span>
                                        {{ `${i18n.type} (${totalRoleSelected})` }}
                                    </span>

                                    <span class="icon is-small">
                                        <i
                                            :class="icon.angleDown"
                                            aria-hidden="true"
                                        />
                                    </span>
                                </template>

                                <biz-dropdown-item
                                    v-for="status in data.roleOptions"
                                    :key="status.id"
                                >
                                    <biz-checkbox
                                        v-model:checked="search.roles"
                                        :value="status.id"
                                        @change="getRecords()"
                                    >
                                        &nbsp; {{ status.value }}
                                    </biz-checkbox>
                                </biz-dropdown-item>
                            </biz-dropdown>
                        </div>
                    </div>
                </biz-panel-block>

                <template v-if="records.length > 0">
                    <biz-panel-block
                        v-for="record in records"
                        :key="record.id"
                    >
                        <div
                            class="media"
                            style="width: 100%"
                        >
                            <biz-image
                                class="media-left"
                                ratio="is-64x64"
                                rounded="is-rounded"
                                :src="record.profile_photo_url ?? userImage"
                            />

                            <div class="media-content">
                                <div class="content">
                                    <p>
                                        <strong>{{ record.full_name }}</strong>
                                        <br>

                                        <span>
                                            {{ record.email }}
                                        </span>
                                        <br>

                                        <span class="is-size-7 has-text-grey">
                                            {{ `${i18n.registered}, ${record.registered_at}` }}
                                        </span>
                                        <br>
                                    </p>
                                </div>
                            </div>

                            <div class="media-right">
                                <biz-button-link
                                    v-if="data.permissions.edit"
                                    class="is-primary is-outlined is-small"
                                    :href="route(data.baseRouteName+'.edit', record.id)"
                                >
                                    {{ i18n.view_detail }}
                                </biz-button-link>
                            </div>
                        </div>
                    </biz-panel-block>
                </template>

                <template v-else>
                    <biz-panel-block>
                        {{ i18n.no_data }}
                    </biz-panel-block>
                </template>

                <biz-panel-block>
                    <div
                        class="level"
                        style="width: 100%"
                    >
                        <div class="level-left" />
                        <div class="level-right">
                            <biz-button-link
                                class="is-primary is-outlined is-small"
                                :href="route(data.baseRouteName+'.index')"
                            >
                                {{ i18n.view_all }}
                            </biz-button-link>
                        </div>
                    </div>
                </biz-panel-block>
            </template>
        </biz-panel>
    </div>
</template>

<script>
    import BizButtonLink from '@/Biz/ButtonLink.vue';
    import BizCheckbox from '@/Biz/Checkbox.vue';
    import BizDropdown from '@/Biz/Dropdown.vue';
    import BizDropdownItem from '@/Biz/DropdownItem.vue';
    import BizImage from '@/Biz/Image.vue';
    import BizInput from '@/Biz/Input.vue';
    import BizLoader from '@/Biz/Loader.vue';
    import BizPanel from '@/Biz/Panel.vue';
    import BizPanelBlock from '@/Biz/PanelBlock.vue';
    import icon from '@/Libs/icon-class';
    import { debounce } from 'lodash';
    import { debounceTime } from '@/Libs/defaults';
    import { userImage } from '@/Libs/defaults';
    import { capitalCase } from 'change-case';

    export default {
        name: 'LatestRegistration',

        components: {
            BizButtonLink,
            BizCheckbox,
            BizDropdown,
            BizDropdownItem,
            BizImage,
            BizInput,
            BizLoader,
            BizPanel,
            BizPanelBlock,
        },

        props: {
            data: { type: Object, required: true },
            i18n: { type: Object, default: () => ({
                add_new : 'Add new',
                type : 'Type',
                view_detail : 'View detail',
                view_all : 'View all',
                registered : 'Registered',
                no_data : 'No data',
                search : 'Search',
            }) },
            title: { type: String, default: "" },
        },

        data() {
            return {
                icon,
                isLoading: false,
                records: [],
                search: {
                    term: null,
                    roles: [],
                },
                userImage: userImage,
            };
        },

        computed: {
            totalRoleSelected() {
                return this.search.roles.length;
            },
        },

        mounted() {
            this.getRecords();
        },

        methods: {
            getRecords() {
                const self = this;

                self.isLoading = true,

                axios.get(
                    route('admin.api.widget.latest-registrations'),
                    {
                        params: {
                            term: self.search.term,
                            roles: self.search.roles,
                        },
                    }
                )
                    .then((response) => {
                        self.records = response.data.records;
                    })
                    .then(() => {
                        self.isLoading = false;
                    });
            },

            onSearch: debounce(function(term = '') {
                if (term.length > 2 || term.length == 0) {
                    this.getRecords();
                }
            }, debounceTime),

            capitalCase,
        }
    }
</script>