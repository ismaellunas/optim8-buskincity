<template>
    <div>
        <div class="box">
            <template v-if="isTabEnable">
                <biz-provide-inject-tabs
                    v-model="activeTab"
                    class="is-boxed"
                >
                    <biz-provide-inject-tab
                        tab-id="user-list-tab-trigger"
                        :title="i18n.users"
                    >
                        <user-list
                            :page-query-params="pageQueryParams"
                            :records="records"
                        />
                    </biz-provide-inject-tab>

                    <biz-provide-inject-tab
                        v-if="can.manageTrashed"
                        tab-id="delete-user-tab-trigger"
                        :title="capitalCase(i18n.deleted_users)"
                    >
                        <user-list-deleted />
                    </biz-provide-inject-tab>
                </biz-provide-inject-tabs>
            </template>

            <template v-else>
                <user-list
                    :page-query-params="pageQueryParams"
                    :records="records"
                />
            </template>
        </div>
    </div>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout.vue';
    import BizProvideInjectTab from '@/Biz/ProvideInjectTab/Tab.vue';
    import BizProvideInjectTabs from '@/Biz/ProvideInjectTab/Tabs.vue';
    import UserList from './List.vue';
    import UserListDeleted from './ListDeleted.vue';
    import { capitalCase } from 'change-case';

    export default {
        name: 'UserIndex',

        components: {
            BizProvideInjectTab,
            BizProvideInjectTabs,
            UserList,
            UserListDeleted,
        },

        provide() {
            return {
                baseRouteName: this.baseRouteName,
                can: this.can,
                roleOptions: this.roleOptions,
                i18n: this.i18n,
            };
        },

        layout: AppLayout,

        props: {
            baseRouteName: { type: String, required: true },
            can: { type: Object, required: true },
            pageQueryParams: { type: Object, required: true },
            records: { type: Object, default: () => {} },
            roleOptions: { type: Object, default: () => {} },
            title: { type: String, required: true },
            i18n: { type: Object, default: () => ({
                users : 'Users',
                deleted_users : 'Deleted users',
            }) },
        },

        data() {
            return {
                activeTab: 0,
            };
        },

        computed: {
            isTabEnable() {
                return this.can.manageTrashed;
            },
        },

        methods: {
            capitalCase,
        }
    };
</script>
