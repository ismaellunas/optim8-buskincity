<template>
    <div>
        <div class="columns">
            <div class="column">
                <div class="is-pulled-left">
                    <biz-filter-search
                        v-model="term"
                        @search="search"
                    />
                </div>
            </div>
            <div class="column">
                <div class="is-pulled-right">
                    <biz-button-link
                        :href="route(baseRouteName + '.create', formBuilder.id)"
                        class="is-primary"
                    >
                        <span class="icon is-small">
                            <i :class="icon.add" />
                        </span>
                        <span>Add New</span>
                    </biz-button-link>
                </div>
            </div>
        </div>

        <biz-table-index
            :is-ajax="true"
            :records="records"
            :query-params="queryParams"
            @on-clicked-pagination="getSettingNotifications"
        >
            <template #thead>
                <tr>
                    <th>Name</th>
                    <th>Subject</th>
                    <th>Status</th>
                    <th>
                        <div class="level-right">
                            Actions
                        </div>
                    </th>
                </tr>
            </template>

            <tr
                v-for="record in records.data"
                :key="record.id"
            >
                <td>{{ record.name }}</td>
                <td>{{ record.subject }}</td>
                <td>
                    <biz-tag
                        :class="{ 'is-success': record.is_active, 'is-dark': !record.is_active }"
                    >
                        {{ record.status }}
                    </biz-tag>
                </td>
                <td>
                    <div class="level-right">
                        <biz-button-link
                            :href="route(baseRouteName + '.edit', {'form_builder': formBuilder.id, 'notification': record.id})"
                            class="is-ghost has-text-black"
                        >
                            <span class="icon is-small">
                                <i :class="icon.edit" />
                            </span>
                        </biz-button-link>
                        <biz-button
                            type="button"
                            class="is-ghost has-text-black ml-1"
                            @click="onDelete(record)"
                        >
                            <span class="icon is-small">
                                <i :class="icon.remove" />
                            </span>
                        </biz-button>
                    </div>
                </td>
            </tr>
        </biz-table-index>
    </div>
</template>

<script>
    import MixinFilterDataHandle from '@/Mixins/FilterDataHandle';
    import MixinHasLoader from '@/Mixins/HasLoader';
    import BizButton from '@/Biz/Button';
    import BizButtonLink from '@/Biz/ButtonLink';
    import BizFilterSearch from '@/Biz/Filter/Search';
    import BizTableIndex from '@/Biz/TableIndex';
    import BizTag from '@/Biz/Tag';
    import icon from '@/Libs/icon-class';
    import { confirmDelete, oops as oopsAlert, success as successAlert } from '@/Libs/alert';
    import { usePage } from '@inertiajs/inertia-vue3';

    export default {
        name: 'Notification',

        components: {
            BizButton,
            BizButtonLink,
            BizFilterSearch,
            BizTableIndex,
            BizTag,
        },

        mixins: [
            MixinFilterDataHandle,
            MixinHasLoader,
        ],

        setup() {
            return {
                baseRouteNameSetting: usePage().props.value.baseRouteNameSetting,
                formBuilder: usePage().props.value.formBuilder,
            };
        },

        data() {
            return {
                icon,
                term: null,
                queryParams: {},
                records: {},
            };
        },

        computed: {
            baseRouteName() {
                return this.baseRouteNameSetting + '.notifications';
            },
        },

        mounted() {
            this.getSettingNotifications();
        },

        methods: {
            getSettingNotifications(url = null) {
                const self = this;
                url = url ?? route(self.baseRouteName + '.records', self.formBuilder.id);

                self.onStartLoadingOverlay();

                axios.get(url, {
                    params: self.queryParams,
                }).then((response) => {
                    self.records = response.data;
                }).then(() => {
                    self.onEndLoadingOverlay();
                });
            },

            search() {
                this.queryParams['term'] = this.term;
                this.getSettingNotifications();
            },

            onDelete(record) {
                const self = this;

                confirmDelete().then(result => {
                    if (result.isConfirmed) {
                        self.$inertia.delete(
                            route(this.baseRouteName+'.destroy', {
                                'form_builder': self.formBuilder.id,
                                'notification': record.id
                            }),
                            {
                                onError: () => {
                                    oopsAlert();
                                },
                                onSuccess: (page) => {
                                    successAlert(page.props.flash.message);
                                    self.getSettingNotifications();
                                },
                            }
                        );
                    }
                })
            },
        }
    };
</script>
