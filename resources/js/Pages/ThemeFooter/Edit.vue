<template>
    <div>
        <biz-error-notifications :errors="$page.props.errors" />

        <div class="box mb-6">
            <biz-tab>
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

                <biz-button
                    type="button"
                    class="is-primary ml-2"
                    @click="onSave(activeTab)"
                >
                    <span>
                        {{ i18n.save }}
                    </span>
                </biz-button>
            </biz-tab>

            <layout
                v-if="activeTab == 'layout'"
                ref="layout"
                :settings="settings"
                :social-media-menus="socialMediaMenus"
            />

            <navigation
                v-if="activeTab == 'navigation'"
                ref="navigation"
                :footer-menus="footerMenus"
                :menu="menu"
            />
        </div>
    </div>
</template>

<script>
    import MixinHasTab from '@/Mixins/HasTab';
    import AppLayout from '@/Layouts/AppLayout.vue';
    import Layout from './Layout.vue';
    import Navigation from './Navigation.vue';
    import BizButton from '@/Biz/Button.vue';
    import BizErrorNotifications from '@/Biz/ErrorNotifications.vue';
    import BizTab from '@/Biz/Tab.vue';
    import BizTabList from '@/Biz/TabList.vue';
    import { confirmLeaveProgress } from '@/Libs/alert';

    export default {
        name: 'ThemeFooterEdit',

        components: {
            Layout,
            Navigation,
            BizButton,
            BizErrorNotifications,
            BizTab,
            BizTabList,
        },

        mixins: [
            MixinHasTab,
        ],

        provide() {
            return {
                i18n: this.i18n,
            };
        },

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
            i18n: { type: Object, default: () => ({
                layout : 'Layout',
                navigation : 'Navigation',
                save : 'Save',
            }) },
        },

        setup(props) {
            return {
                tabs: {
                    layout: { title: props.i18n.layout, id: 'layout-tab-trigger' },
                    navigation: { title: props.i18n.navigation, id: 'navigation-tab-trigger' },
                },
            }
        },

        data() {
            return {
                activeTab: 'layout',
            };
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

            onSave(tab) {
                if (tab == 'layout') {
                    this.$refs.layout.onSubmit();
                }

                if (tab == 'navigation') {
                    this.$refs.navigation.updateMenuItems();
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
