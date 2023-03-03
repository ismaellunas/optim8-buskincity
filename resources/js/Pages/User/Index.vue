<template>
    <div>
        <div class="box">
            <template v-if="isTabEnable">
                <biz-provide-inject-tabs
                    v-model="activeTab"
                    class="is-boxed"
                >
                    <biz-provide-inject-tab title="Users">
                        <user-list
                            :page-query-params="pageQueryParams"
                            :records="records"
                        />
                    </biz-provide-inject-tab>

                    <biz-provide-inject-tab
                        v-if="can.manageTrashed"
                        title="Deleted Users"
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
    };
</script>
