<template>
    <biz-tab class="is-boxed">
        <ul>
            <biz-tab-list
                v-for="(tab, index) in tabs"
                :key="index"
                :is-active="isTabActive(index)"
            >
                <biz-link
                    :href="tab.url"
                    @click.prevent="setActiveTab(index)"
                >
                    {{ tab.title }}
                </biz-link>
            </biz-tab-list>
        </ul>
    </biz-tab>
</template>

<script>
    import MixinHasTab from '@/Mixins/HasTab';
    import BizLink from '@/Biz/Link.vue';
    import BizTab from '@/Biz/Tab.vue';
    import BizTabList from '@/Biz/TabList.vue';

    export default {
        name: 'ModuleEditTabs',

        components: {
            BizLink,
            BizTab,
            BizTabList,
        },

        mixins: [
            MixinHasTab,
        ],

        props: {
            tabIndex: { type: Number, default: 0 },
            tabs: { type: Array, required: true },
        },

        setup(props) {
            return {
                activeTab: props.tabIndex,
            };
        },
    };
</script>
