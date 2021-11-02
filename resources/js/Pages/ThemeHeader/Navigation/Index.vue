<template>
    <app-layout>
        <template #header>
            {{ title }}
        </template>

        <sdb-error-notifications :errors="$page.props.errors"/>

        <form method="post">
            <div class="box mb-3">
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
                </sdb-tab>
                <div class="columns">
                    <div class="column">
                        <div class="is-pulled-left">
                            <b>Menu Items</b><br>
                            Last Saved: {{ menuItemLastSaved }}
                        </div>
                    </div>
                    <div class="column">
                        <div class="is-pulled-right">
                            <sdb-button
                                type="button"
                                class="is-primary ml-2"
                                @click="updateFormatMenu()"
                            >
                                <span>Save</span>
                            </sdb-button>
                        </div>
                    </div>
                </div>
                <div class="columns">
                    <div class="column">
                        <menu-nested
                            :items="items"
                            :isChild="false"
                            @change="checkNestedMenu"
                            @delete-row="deleteRow"
                            @edit-row="editRow"
                            @open-form-modal="openFormModal()"
                        ></menu-nested>
                    </div>
                </div>
            </div>
        </form>
        <modal-form-menu-item
            v-if="isModalOpen"
            :base-route-name="baseRouteName"
            :menu="menu"
            :menuItem="selectedMenuItems"
            @close="closeModal()"
            @sync-menu-items="syncMenuItems()"
        ></modal-form-menu-item>
    </app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import MenuNested from './MenuNested';
    import MixinHasModal from '@/Mixins/HasModal';
    import MixinHasTab from '@/Mixins/HasTab';
    import ModalFormMenuItem from './FormMenuItem';
    import SdbButton from '@/Sdb/Button';
    import SdbButtonLink from '@/Sdb/ButtonLink';
    import SdbErrorNotifications from '@/Sdb/ErrorNotifications';;
    import SdbFormInput from '@/Sdb/Form/Input';
    import SdbTab from '@/Sdb/Tab';
    import SdbTabList from '@/Sdb/TabList';
    import { useForm } from '@inertiajs/inertia-vue3';
    import { confirmDelete, oops as oopsAlert, success as successAlert  } from '@/Libs/alert';
    import { merge, forEach, cloneDeep } from 'lodash';

    export default {
        components: {
            AppLayout,
            MenuNested,
            ModalFormMenuItem,
            SdbButton,
            SdbButtonLink,
            SdbErrorNotifications,
            SdbFormInput,
            SdbTab,
            SdbTabList,
        },

        mixins: [
            MixinHasModal,
            MixinHasTab,
        ],

        props: {
            baseRouteName: {
                type: String,
                required: true,
            },
            menu: {
                type: Object,
                required: true,
            },
            menuItemLastSaved: {
                type: String,
                default: "-",
            },
            menuItems: {
                type: Array,
                default: [],
            },
            title: {
                type: String,
                default: "-"
            },
        },

        setup(props) {
            const form = merge(
                props.menu,
                {
                    menu_items: {
                        en: [],
                    }
                }
            );
            return {
                form: useForm(form),
                tabs: {
                    layout: { title: 'Layout'},
                    navigation: {title: 'Navigation'},
                },
            }
        },

        data() {
            return {
                activeTab: 'navigation',
                items: [],
                lastMenuItems: [],
                selectedMenuItems: {},
            };
        },

        mounted() {
            this.syncMenuItems();
        },

        methods: {
            onTabSelected(tab) {
                let routeName = this.baseRouteName+'.index';
                if (tab === 'layout') {
                    routeName = 'admin.theme.header.index';
                }
                this.$inertia.get(route(routeName));
            },

            openFormModal() {
                this.selectedMenuItems = {};
                this.isModalOpen = true;
            },

            checkNestedMenu() {
                let self = this;
                forEach(self.items, function(values) {
                    forEach(values.children, function(value) {
                        if (value['children'].length > 0) {
                            self.items = self.lastMenuItems;
                            oopsAlert(null, "Cannot add nested menu more than 2");
                        }
                    });
                });

                self.updateLastDataMenu();
            },

            updateLastDataMenu() {
                this.lastMenuItems = cloneDeep(this.items);
            },

            deleteRow(menuItemId) {
                const self = this;
                confirmDelete().then((result) => {
                    if (result.isConfirmed) {
                        self.$inertia.delete(
                            route(self.baseRouteName+'.destroy', menuItemId), {
                                preserveState: true,
                                onFinish: () => {
                                    self.syncMenuItems();
                                }
                            }
                        );
                    }
                });
            },

            editRow(menuItem) {
                this.selectedMenuItems = menuItem;
                this.openModal();
            },

            syncMenuItems() {
                this.items = this.menuItems;
                this.updateLastDataMenu();
            },

            updateFormatMenu() {
                this.$inertia.put(route(this.baseRouteName+'.update.format'), this.items, {
                    onSuccess: (page) => {
                        successAlert(page.props.flash.message);
                        this.syncMenuItems();
                    },
                });
            },
        }
    }
</script>
