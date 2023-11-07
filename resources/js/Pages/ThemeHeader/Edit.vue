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
                :header-menus="headerMenus"
                :menu="menu"
            />

            <layout
                v-if="activeTab == 'layout'"
                ref="layout"
                :logo-media="logoMedia"
                :settings="settings"
                :instructions="instructions"
            />
        </div>
    </div>
</template>

<script>
    import MixinHasTab from '@/Mixins/HasTab';
    import AppLayout from '@/Layouts/AppLayout.vue';
    import BizErrorNotifications from '@/Biz/ErrorNotifications.vue';
    import BizTab from '@/Biz/Tab.vue';
    import BizTabList from '@/Biz/TabList.vue';
    import Layout from './Layout.vue';
    import Navigation from './Navigation.vue';
    import { confirmLeaveProgress } from '@/Libs/alert';
    import { computed, ref } from 'vue';

    export default {
        name: "ThemeHeaderEdit",

        components: {
            BizErrorNotifications,
            BizTab,
            BizTabList,
            Layout,
            Navigation,
        },

        mixins: [
            MixinHasTab,
        ],

        provide() {
            return {
                can: this.can,
                dimensions: this.dimensions,
                instructions: this.instructions,
            };
        },

        layout: AppLayout,

        props: {
            baseRouteName: { type: String, required: true },
            can: { type: Object, default: () => {} },
            dimensions: { type: Object, default: () => {} },
            headerMenus: { type: Object, default: () => {} },
            i18n: { type: Object, default: () => {}},
            instructions: { type: Object, default: () => {} },
            logoMedia: { type: Object, default: () => {} },
            menu: { type: Object, required: true },
            settings: { type: Object, required: true },
            title: { type: String, default: "-" },
        },

        setup(props) {
            let i18n = computed(() => props.i18n);

            return {
                activeTab: ref('navigation'),
                tabs: {
                    navigation: { title: i18n.value.navigation, id: 'navigation-tab-trigger' },
                    layout: { title: i18n.value.layout, id: 'layout-tab-trigger' },
                },
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
                });
            },
        }
    };
</script>
