<template>
    <app-layout>
        <template #header>
            {{ title }}
        </template>

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

            <footer-layout
                v-if="isLayout"
                ref="footerLayout"
                :setting="settings.footer_layout"
            />

            <footer-navigation
                v-if="isNavigation"
                ref="footerNavigation"
                :last-saved="menuItemLastSaved"
                :menu-items="menuItems"
                :menu="menu"
            />
        </div>
    </app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import FooterLayout from './FooterLayout';
    import FooterNavigation from './FooterNavigation';
    import MixinHasTab from '@/Mixins/HasTab';
    import SdbButton from '@/Sdb/Button';
    import SdbTab from '@/Sdb/Tab';
    import SdbTabList from '@/Sdb/TabList';

    export default {
        components: {
            AppLayout,
            FooterLayout,
            FooterNavigation,
            SdbButton,
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
                default:() => {},
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
        computed: {
            isLayout() {
                return this.activeTab == 'layout';
            },
            isNavigation() {
                return this.activeTab == 'navigation';
            },
        },
        methods: {
            onTabSelected(tab) {
                this.activeTab = tab;
            },
            onSave(tab) {
                if (tab == 'layout') {
                    this.$refs.footerLayout.saveLayout();
                }

                if (tab == 'navigation') {
                    this.$refs.footerNavigation.updateMenuItems();
                }
            },
        }
    }
</script>
