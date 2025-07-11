<template>
    <div>
        <biz-error-notifications :errors="$page.props.errors" />

        <div class="box mb-6">
            <biz-tab class="is-boxed">
                <ul>
                    <biz-tab-list
                        v-for="(tab, index) in tabs"
                        :id="tab.id"
                        :key="index"
                        :is-active="isTabActive(index)"
                    >
                        <a @click.prevent="setActiveTab(index)">
                            {{ tab.title }}
                        </a>
                    </biz-tab-list>
                </ul>
            </biz-tab>

            <navigation
                v-if="activeTab == 'navigation'"
                ref="navigation"
                :footer-menus="footerMenus"
                :menu="menu"
            />

            <layout
                v-if="activeTab == 'layout'"
                ref="layout"
                :settings="settings"
                :social-media-menus="socialMediaMenus"
            />
        </div>
    </div>
</template>

<script>
    import MixinHasTab from '@/Mixins/HasTab';
    import AppLayout from '@/Layouts/AppLayout.vue';
    import Layout from './Layout.vue';
    import Navigation from './Navigation.vue';
    import BizErrorNotifications from '@/Biz/ErrorNotifications.vue';
    import BizTab from '@/Biz/Tab.vue';
    import BizTabList from '@/Biz/TabList.vue';
    import { confirmLeaveProgress } from '@/Libs/alert';
    import { computed, ref } from 'vue';

    export default {
        name: 'ThemeFooterEdit',

        components: {
            Layout,
            Navigation,
            BizErrorNotifications,
            BizTab,
            BizTabList,
        },

        mixins: [
            MixinHasTab,
        ],

        layout: AppLayout,

        props: {
            baseRouteName: {
                type: String,
                required: true
            },
            menu: {
                type: Object,
                required: true,
            },
            footerMenus: {
                type: Object,
                default() {
                    return {};
                },
            },
            settings: {
                type: Object,
                required: true
            },
            socialMediaMenus: {
                type: Array,
                default:() => [],
            },
            title: {
                type: String,
                default: "-"
            },
            i18n: { type: Object, default: () => {} },
        },

        setup(props) {
            let i18n = computed(() => props.i18n);

            return {
                tabs: {
                    navigation: { title: i18n.value.navigation, id: 'navigation-tab-trigger' },
                    layout: { title: i18n.value.layout, id: 'layout-tab-trigger' },
                },
                activeTab: ref('navigation'),
            }
        },

        methods: {
            setActiveTab(tab) {
                if (tab === this.activeTab) {
                    return false;
                }

                if (tab == 'layout') {
                    if (this.$refs.navigation.isFormDirty()) {
                        this.confirmAlert(tab);
                    } else {
                        this.activeTab = tab;
                    }
                }

                if (tab == 'navigation') {
                    if (this.$refs.layout.isFormDirty()) {
                        this.confirmAlert(tab)
                    } else {
                        this.activeTab = tab;
                    }
                }
            },

            confirmAlert(tab) {
                confirmLeaveProgress().then((result) => {
                    if (result.isDismissed) {
                        return false;
                    } else if (result.isConfirmed) {
                        this.activeTab = tab;
                    }
                })
            },
        }
    };
</script>
