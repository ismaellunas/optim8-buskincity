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

            <layout
                v-if="isLayout"
                ref="layout"
                :settings="settings"
            />

            <!-- <footer-navigation
                v-if="isNavigation"
                ref="footerNavigation"
                :last-saved="menuItemLastSaved"
                :menu-items="footerMenus"
                :menu="menu"
            /> -->
        </div>
    </app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import Layout from './Layout';
    import FooterNavigation from './FooterNavigation';
    import MixinHasTab from '@/Mixins/HasTab';
    import SdbButton from '@/Sdb/Button';
    import SdbTab from '@/Sdb/Tab';
    import SdbTabList from '@/Sdb/TabList';

    export default {
        components: {
            AppLayout,
            Layout,
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
            footerMenus: {
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
                if (tab == 'layout') {
                    this.activeTab = tab;
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
                    this.$refs.footerNavigation.updateMenuItems();
                }
            },
            confirmAlert(tab) {
                const confirmationMessage = (
                    'It looks like you have been editing something. '
                    + 'If you leave before saving, your changes will be lost.'
                );

                this.$swal.fire({
                    title: 'Are you sure?',
                    text: confirmationMessage,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Leave this',
                    cancelButtonText: 'Continue Editing',
                    scrollbarPadding: false,
                }).then((result) => {
                    if (result.isDismissed) {
                        return false;
                    } else if (result.isConfirmed) {
                        this.activeTab = tab;
                    }
                })
            },
        }
    }
</script>
