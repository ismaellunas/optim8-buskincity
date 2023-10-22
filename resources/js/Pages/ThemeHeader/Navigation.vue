<template>
    <form @submit.prevent="onSubmit">
        <div class="columns">
            <div class="column">
                <span class="has-text-weight-bold">
                    {{ i18n.menu_items }}
                </span>

                <p class="help is-info">
                    {{ i18n.menu_items_note }}
                </p>
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
                    @duplicate-menu-item="openDuplicateModal"
                    @edit-row="editRow"
                    @update-last-data-menu-items="updateLastDataMenuItems"
                />
            </div>
        </div>

        <div class="field is-grouped is-justify-content-space-between">
            <div class="control">
                <biz-button-icon
                    type="button"
                    class="is-primary"
                    :icon="iconAdd"
                    @click="openFormModal()"
                >
                    <span>
                        {{ sentenceCase(i18n.add_menu_item) }}
                    </span>
                </biz-button-icon>
            </div>

            <div class="control">
                <biz-button class="is-primary">
                    {{ i18n.save }}
                </biz-button>
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
    </form>
</template>

<script>
    import MixinHasModal from '@/Mixins/HasModal';
    import MixinHasLoader from '@/Mixins/HasLoader';
    import BizLanguageTab from '@/Biz/LanguageTab.vue';
    import BizButton from '@/Biz/Button.vue';
    import BizButtonIcon from '@/Biz/ButtonIcon.vue';
    import NavigationFormDuplicate from './NavigationFormDuplicate.vue';
    import NavigationFormMenu from './NavigationFormMenuItem.vue';
    import NavigationMenu from './NavigationMenu.vue';
    import { usePage, useForm } from '@inertiajs/vue3';
    import { oops as oopsAlert, success as successAlert, confirmLeaveProgress } from '@/Libs/alert';
    import { forEach, cloneDeep } from 'lodash';
    import { computed } from 'vue';
    import { sentenceCase } from 'change-case';
    import { add as iconAdd } from '@/Libs/icon-class';

    export default {
        name: 'ThemeHeaderNavigation',

        components: {
            BizLanguageTab,
            BizButton,
            BizButtonIcon,
            NavigationFormDuplicate,
            NavigationFormMenu,
            NavigationMenu,
        },

        mixins: [
            MixinHasModal,
            MixinHasLoader,
        ],

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
                i18n: computed(() => usePage().props.i18n),
                iconAdd,
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

            onSubmit() {
                const self = this;
                this.menuForm.post(
                    route(this.baseRouteName+'.navigation.update'),
                    {
                        preserveScroll: true,
                        errorBag: 'navigation',
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

            sentenceCase,
        }
    };
</script>
