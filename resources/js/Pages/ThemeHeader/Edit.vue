<template>
    <app-layout>
        <template #header>
            {{ title }}
        </template>

        <sdb-error-notifications :errors="$page.props.errors" />

        <div class="box mb-6">
            <sdb-tab>
                <ul>
                    <sdb-tab-list
                        v-for="(tab, index) in tabs"
                        :key="index"
                        :is-active="isTabActive(index)"
                    >
                        <a @click.prevent="setActiveTab(index)">
                            {{ tab.title }}
                        </a>
                    </sdb-tab-list>
                </ul>

                <sdb-button
                    type="button"
                    class="is-primary ml-2"
                    @click="onSave(activeTab)"
                >
                    <span>Save</span>
                </sdb-button>
            </sdb-tab>

            <layout
                v-if="activeTab == 'layout'"
                ref="layout"
                :settings="settings"
            />

            <navigation
                v-if="activeTab == 'navigation'"
                ref="navigation"
                :last-saved="menuItemLastSaved"
                :menu-items="menuItems"
                :menu="menu"
            />
        </div>
    </app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import Layout from './Layout';
    import Navigation from './Navigation';
    import MixinHasTab from '@/Mixins/HasTab';
    import SdbButton from '@/Sdb/Button';
    import SdbErrorNotifications from '@/Sdb/ErrorNotifications';
    import SdbTab from '@/Sdb/Tab';
    import SdbTabList from '@/Sdb/TabList';

    export default {
        components: {
            AppLayout,
            Layout,
            Navigation,
            SdbButton,
            SdbErrorNotifications,
            SdbTab,
            SdbTabList,
        },
        mixins: [
            MixinHasTab,
        ],
        props: {
            baseRouteName: {
                type: String,
                required: true
            },
            menu: {
                type: Object,
                required: true,
            },
            menuItems: {
                type: Object,
                default() {
                    return {};
                },
            },
            menuItemLastSaved: {
                type: String,
                default: "-",
            },
            settings: {
                type: Object,
                required: true
            },
            title: {
                type: String,
                default: "-"
            },
        },
        setup() {
            return {
                tabs: {
                    layout: {title: 'Layout'},
                    navigation: {title: 'Navigation'},
                },
            }
        },
        data() {
            return {
                activeTab: 'layout',
            };
        },
        methods: {
            onTabSelected(tab) {
                this.activeTab = tab;
            },
            onSave(tab) {
                if (tab == 'layout') {
                    this.$refs.layout.$refs.headerLayout.saveLayout();
                }

                if (tab == 'navigation') {
                    this.$refs.navigation.updateFormatMenu();
                }
            },
        }
    }
</script>
