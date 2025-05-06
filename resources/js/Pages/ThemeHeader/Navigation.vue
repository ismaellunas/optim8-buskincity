<template>
    <section>
        <div class="columns">
            <div class="column">
                <div class="is-pulled-left">
                    <b>
                        {{ i18n.menu_items }}
                    </b>
                </div>
            </div>
            <div class="column">
                <biz-language-tab
                    class="is-right"
                    :locale-options="localeOptions"
                    :selected-locale="selectedLocale"
                    @on-change-locale="onChangeLocale"
                />
            </div>
        </div>
        <div class="columns">
            <div class="column">
                <navigation-menu
                    :is-child="false"
                    :menu-items="menuForm.menu_items"
                    :locale-options="localeOptions"
                    :selected-locale="selectedLocale"
                    @change="checkNestedMenuItems"
                    @duplicate-menu-item="openDuplicateModal"
                    @edit-row="editRow"
                    @open-form-modal="openFormModal()"
                    @update-last-data-menu-items="updateLastDataMenuItems"
                />
            </div>
        </div>

        <navigation-form-menu
            v-if="isModalOpen"
            :base-route-name="baseRouteName"
            :errors="menuItemErrors"
            :menu="menu"
            :menu-item="selectedMenuItem"
            :selected-locale="selectedLocale"
            @add-menu-item="addMenuItem"
            @close="closeModal()"
            @update-menu-item="updateMenuItem"
        />

        <navigation-form-duplicate
            v-if="isModalDuplicateOpen"
            :errors="menuItemErrors"
            :locale-options="localeOptions"
            :menu-item="selectedMenuItem"
            @close="closeDuplicateModal()"
            @duplicate-menu-item="duplicateMenuItem"
        />
    </section>
</template>

<script>
    import MixinHasModal from '@/Mixins/HasModal';
    import MixinHasLoader from '@/Mixins/HasLoader';
    import BizLanguageTab from '@/Biz/LanguageTab.vue';
    import NavigationFormDuplicate from './NavigationFormDuplicate.vue';
    import NavigationFormMenu from './NavigationFormMenuItem.vue';
    import NavigationMenu from './NavigationMenu.vue';
    import { usePage, useForm } from '@inertiajs/vue3';
    import { oops as oopsAlert, success as successAlert, confirmLeaveProgress } from '@/Libs/alert';
    import { forEach, cloneDeep } from 'lodash';

    export default {
        name: 'ThemeHeaderNavigation',

        components: {
            BizLanguageTab,
            NavigationFormDuplicate,
            NavigationFormMenu,
            NavigationMenu,
        },

        mixins: [
            MixinHasModal,
            MixinHasLoader,
        ],

        inject: {
            i18n: { default: () => ({
                menu_items : 'Menu items',
            }) }
        },

        props: {
            menu: {
                type: Object,
                required: true,
            },
            headerMenus: {
                type: Object,
                default:() => {},
            },
            title: {
                type: String,
                default: "-"
            },
        },

        setup() {
            return {
                baseRouteName: usePage().props.baseRouteName ?? null,
                localeOptions: usePage().props.languageOptions ?? [],
                defaultLocale: usePage().props.defaultLanguage,
                tabs: {
                    layout: { title: 'Layout'},
                    navigation: {title: 'Navigation'},
                },
            }
        },

        data() {
            return {
                activeTab: 'navigation',
                isModalDuplicateOpen: false,
                lastDataMenuItems: [],
                menuForm: {},
                selectedLocale: this.defaultLocale,
                selectedMenuItem: {},
                menuItemErrors: {},
                validationRoute: route('admin.api.theme.header.menu-item.validate'),
            };
        },

        mounted() {
            this.menuForm = this.getMenuForm(this.selectedLocale);
            this.updateLastDataMenuItems();
        },

        methods: {
            isFormDirty() {
                return this.menuForm.isDirty;
            },

            getMenuForm(locale) {
                return useForm({
                    locale: locale,
                    menu_items: cloneDeep(this.headerMenus[locale]),
                });
            },

            onChangeLocale(locale) {
                if (this.menuForm.isDirty) {
                    confirmLeaveProgress().then((result) => {
                        if (result.isDismissed) {
                            return false;
                        } else if (result.isConfirmed) {
                            this.selectedLocale = locale;
                            this.menuForm.reset();
                            this.menuForm = this.getMenuForm(locale);

                            this.updateLastDataMenuItems();
                        }
                    });
                } else {
                    this.selectedLocale = locale;

                    this.menuForm = this.getMenuForm(locale);
                    this.updateLastDataMenuItems();
                }
            },

            openFormModal() {
                this.selectedMenuItem = {};
                this.menuItemErrors = {};
                this.isModalOpen = true;
            },

            openDuplicateModal(menuItem) {
                const cloneMenuItem = cloneDeep(menuItem);
                cloneMenuItem['id'] = null;
                cloneMenuItem['parent_id'] = null;
                cloneMenuItem['children'] = [];
                cloneMenuItem['locale'] = this.selectedLocale;

                this.selectedMenuItem = cloneMenuItem;
                this.menuItemErrors = {};
                this.isModalDuplicateOpen = true;
            },

            closeDuplicateModal() {
                this.selectedMenuItem = {};
                this.menuItemErrors = {};
                this.isModalDuplicateOpen = false;
            },

            checkNestedMenuItems() {
                let self = this;
                forEach(self.menuForm.menu_items, function(menuItem) {
                    forEach(menuItem.children, function(child) {
                        if (child['children'].length > 0) {
                            self.menuForm.menu_items = self.lastDataMenuItems;
                            oopsAlert(null, "Cannot add nested menu more than 2");
                        }
                    });
                });

                self.updateLastDataMenuItems();
            },

            updateLastDataMenuItems() {
                this.lastDataMenuItems = cloneDeep(this.menuForm.menu_items);
            },

            updateMenuItem(menuItem) {
                const self = this;

                axios.post(self.validationRoute, menuItem)
                    .then(() => {
                        self.updateSelectedMenu(menuItem);
                        self.updateLastDataMenuItems();
                        self.closeModal();
                    })
                    .catch((error) => {
                        self.menuItemErrors = error.response.data.errors;
                    });
            },

            updateSelectedMenu(menuItem) {
                this.selectedMenuItem['title'] = menuItem['title'];
                this.selectedMenuItem['type'] = menuItem['type'];
                this.selectedMenuItem['url'] = menuItem['url'];
                this.selectedMenuItem['is_blank'] = menuItem['is_blank'];
                this.selectedMenuItem['menu_itemable_id'] = menuItem['menu_itemable_id'];
            },

            addMenuItem(menuItem) {
                const self = this;
                axios.post(self.validationRoute, menuItem)
                    .then(() => {
                        self.menuForm.menu_items.push(
                            cloneDeep(menuItem)
                        );
                        self.updateLastDataMenuItems();
                        self.closeModal();
                    })
                    .catch((error) => {
                        self.menuItemErrors = error.response.data.errors;
                    });
            },

            editRow(menuItem) {
                this.selectedMenuItem = menuItem;
                this.menuItemErrors = {};
                this.openModal();
            },

            updateMenuItems() {
                const self = this;
                this.menuForm.post(route(this.baseRouteName+'.update-menu-item'), {
                    preserveScroll: true,
                    onStart: visit => {
                        self.onStartLoadingOverlay();
                    },
                    onSuccess: (page) => {
                        successAlert(page.props.flash.message);
                        this.updateLastDataMenuItems();
                    },
                    onFinish: visit => {
                        self.onEndLoadingOverlay();
                    }
                });
            },

            duplicateMenuItem(menuItem) {
                const self = this;
                axios.post(self.validationRoute, menuItem)
                    .then(() => {
                        if (
                            self.menuForm.isDirty
                            && self.selectedLocale !== menuItem['locale']
                        ) {
                            confirmLeaveProgress().then((result) => {
                                if (result.isDismissed) {
                                    return false;
                                } else if(result.isConfirmed) {
                                    self.selectedLocale = menuItem['locale'];

                                    self.menuForm = self.getMenuForm(menuItem['locale']);
                                    self.menuForm.menu_items.push(menuItem);

                                    self.closeDuplicateModal();
                                    self.updateLastDataMenuItems();
                                }
                            });
                        } else {
                            if (self.selectedLocale !== menuItem['locale']) {
                                self.selectedLocale = menuItem['locale'];
                                self.menuForm = self.getMenuForm(menuItem['locale']);
                            }

                            self.menuForm.menu_items.push(menuItem);

                            self.closeDuplicateModal();
                            self.updateLastDataMenuItems();
                        }
                    })
                    .catch((error) => {
                        self.menuItemErrors = error.response.data.errors;
                    });
            },
        }
    };
</script>
