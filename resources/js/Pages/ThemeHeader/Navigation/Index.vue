<template>
    <app-layout>
        <template #header>
            Main Menu
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
                                class="is-primary"
                                @click.prevent="newRow()"
                            >
                                <span class="icon is-small">
                                    <i class="fas fa-plus"></i>
                                </span>
                                <span>Add Menu Item</span>
                            </sdb-button>
                        </div>
                    </div>
                </div>
                <div class="columns">
                    <div class="column">
                        <menu-nested
                            :items="items"
                            :isChild="false"
                            @edit-row="editRow"
                            @delete-row="deleteRow"
                            @change="changeMenu"
                        ></menu-nested>
                    </div>
                </div>
            </div>
            <div class="columns">
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
        </form>
        <modal-form-menu-item
            v-if="isModalOpen"
            :base-route-name="baseRouteName"
            :menu="menu"
            :menuItem="selectedMenuItems"
            @close="closeModal()"
        ></modal-form-menu-item>
    </app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import MixinHasModal from '@/Mixins/HasModal';
    import ModalFormMenuItem from './FormMenuItem';
    import MixinHasTab from '@/Mixins/HasTab';
    import SdbTab from '@/Sdb/Tab';
    import SdbTabList from '@/Sdb/TabList';
    import MenuNested from './MenuNested';
    import SdbButton from '@/Sdb/Button';
    import SdbButtonLink from '@/Sdb/ButtonLink';
    import SdbErrorNotifications from '@/Sdb/ErrorNotifications';;
    import SdbFormInput from '@/Sdb/Form/Input';
    import { useForm } from '@inertiajs/inertia-vue3';
    import { confirmDelete, oops as oopsAlert, success as successAlert  } from '@/Libs/alert';
    import { merge, filter, forEach, cloneDeep } from 'lodash';
    import { isEmpty, useModelWrapper } from '@/Libs/utils';

    export default {
        components: {
            AppLayout,
            ModalFormMenuItem,
            MenuNested,
            SdbTab,
            SdbTabList,
            SdbButton,
            SdbButtonLink,
            SdbErrorNotifications,
            SdbFormInput,
        },
        emits: [
            'update:menuItems',
        ],
        mixins: [
            MixinHasModal,
            MixinHasTab,
        ],
        props: {
            menuItemLastSaved: String,
            baseRouteName: String,
            menu: Object,
            menuItems: Object,
        },
        setup(props, { emit }) {
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
                items: useModelWrapper(props, emit, 'menuItems'),
            }
        },
        data() {
            return {
                // items: this.menuItems,
                selectedMenuItems: {},
                lastMenuItems: [],
                activeTab: 'navigation',
                loader: null,
            };
        },
        mounted() {
            this.updateLastDataMenu();
        },
        methods: {
            onTabSelected(tab) {
                let routeName = this.baseRouteName+'.index';
                if (tab === 'layout') {
                    routeName = 'admin.theme.header.index';
                }
                this.$inertia.get(route(routeName));
            },

            newRow() {
                this.selectedMenuItems = {};
                this.isModalOpen = true;
            },

            changeMenu() {
                let self = this;
                forEach(self.items, function(values) {
                    forEach(values.children, function(value) {
                        if (value['children'].length > 0) {
                            // self.items = self.lastMenuItems;
                            self.$emit('update:menuItems', self.lastMenuItems);
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
                            route(self.baseRouteName+'.destroy', menuItemId)
                        );
                    }
                });
            },

            editRow(menuItemId) {
                let menuItem = filter(this.menuItems, { id: menuItemId });
                if (!isEmpty(menuItem)) {
                    this.selectedMenuItems = menuItem[0];
                } else {
                    this.selectedMenuItems = {};
                }

                this.openModal();
            },

            updateFormatMenu() {
                this.$inertia.put(route(this.baseRouteName+'.update.format'), this.items, {
                    onStart: () => {
                        self.loader = self.$loading.show();
                        self.isProcessing = true;
                    },
                    onSuccess: (page) => {
                        successAlert(page.props.flash.message);
                    },
                    onFinish: () => {
                        self.loader.hide();
                        self.isProcessing = false;
                    }
                });
            },
        }
    }
</script>
