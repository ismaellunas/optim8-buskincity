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
                :links="links"
            />

            <navigation
                v-if="activeTab == 'navigation'"
                ref="navigation"
                :last-saved="menuItemLastSaved"
                :footer-menus="footerMenus"
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
            footerMenus: {
                type: Object,
                default() {
                    return {};
                },
            },
            links: {
                type: Array,
                default:() => [],
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
            setActiveTab(tab) {
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
