<template>
    <div>
        <form-section>
            <template #title>
                Notification
            </template>

            <template #form>
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

                <div class="table-container">
                    <biz-table class="is-fullwidth">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Subject</th>
                                <th>
                                    <div class="level-right">
                                        Actions
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="record in records.data"
                                :key="record.id"
                            >
                                <td>{{ record.name }}</td>
                                <td>{{ record.subject }}</td>
                                <td>
                                    <div class="level-right">
                                        <biz-button-link
                                            href="#"
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
                        </tbody>
                    </biz-table>
                </div>

                <biz-pagination
                    :is-ajax="true"
                    :links="records.links"
                    :query-params="queryParams"
                    @on-clicked-pagination="getSettingNotifications"
                />
            </template>
        </form-section>
    </div>
</template>

<script>
    import MixinFilterDataHandle from '@/Mixins/FilterDataHandle';
    import BizButton from '@/Biz/Button';
    import BizButtonLink from '@/Biz/ButtonLink';
    import BizFilterSearch from '@/Biz/Filter/Search';
    import BizPagination from '@/Biz/Pagination';
    import BizTable from '@/Biz/Table';
    import FormSection from '@/Frontend/FormSection';
    import icon from '@/Libs/icon-class';
    import { confirmDelete, oops as oopsAlert, success as successAlert } from '@/Libs/alert';
    import { usePage } from '@inertiajs/inertia-vue3';

    export default {
        name: 'Notification',

        components: {
            BizButton,
            BizButtonLink,
            BizFilterSearch,
            BizPagination,
            BizTable,
            FormSection,
        },

        mixins: [
            MixinFilterDataHandle,
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

                axios.get(url, {
                    params: self.queryParams,
                }).then((response) => {
                    self.records = response.data;
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
                                onStart: self.onStartLoadingOverlay,
                                onFinish: self.onEndLoadingOverlay,
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
