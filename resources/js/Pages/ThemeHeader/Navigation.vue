<template>
    <form @submit.prevent="onSubmit">
        <div class="columns">
            <div class="column">
                <span class="has-text-weight-bold">
                    {{ i18n.menu_items }}
                </span>

                <biz-tooltip :message="i18n.menu_items_note" />
            </div>
            <div class="column">
                <biz-language-tab
                    class="is-right"
                    :locale-options="localeOptions"
                    :selected-locale="selectedLocale"
                    @on-change-locale="changeLocale"
                />
            </div>
        </div>
        <div class="columns is-multiline">
            <div class="column is-12">
                <biz-button-icon
                    type="button"
                    class="is-primary"
                    :icon="iconAdd"
                    @click="openFormMenuModal('front')"
                >
                    <span>
                        {{ sentenceCase(i18n.add_menu_item) }}
                    </span>
                </biz-button-icon>
            </div>

            <div class="column is-12">
                <navigation-menu
                    :is-child="false"
                    :menu-items="menuForm.menu_items"
                    :locale-options="localeOptions"
                    :selected-locale="selectedLocale"
                    @add-child-menu-item="addChildMenuItem"
                    @change="checkNestedMenuItems"
                    @check-nested-menu-items="checkNestedMenuItems"
                    @duplicate-menu-item="openFormDuplicateModal"
                    @edit-row="editRow"
                />
            </div>
        </div>

        <div class="field is-grouped is-justify-content-space-between">
            <div class="control">
                <biz-button-icon
                    type="button"
                    class="is-primary"
                    :icon="iconAdd"
                    @click="openFormMenuModal()"
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
            v-if="isFormDuplicateModalOpen"
            :errors="menuItemErrors"
            :locale-options="localeOptions"
            :menu-item="selectedMenuItem"
            @close="closeFormDuplicateModal()"
            @duplicate-menu-item="duplicateMenuItem"
        />
    </form>
</template>

<script>
    import MixinHasLoader from '@/Mixins/HasLoader';
    import MixinHasModal from '@/Mixins/HasModal';
    import BizButton from '@/Biz/Button.vue';
    import BizButtonIcon from '@/Biz/ButtonIcon.vue';
    import BizLanguageTab from '@/Biz/LanguageTab.vue';
    import BizTooltip from '@/Biz/Tooltip.vue';
    import NavigationFormDuplicate from './NavigationFormDuplicate.vue';
    import NavigationFormMenu from './NavigationFormMenuItem.vue';
    import NavigationMenu from './NavigationMenu.vue';
    import { usePage, useForm } from '@inertiajs/vue3';
    import { oops as oopsAlert, success as successAlert, confirmLeaveProgress } from '@/Libs/alert';
    import { forEach, cloneDeep } from 'lodash';
    import { computed, ref } from 'vue';
    import { sentenceCase } from 'change-case';
    import { add as iconAdd } from '@/Libs/icon-class';

    export default {
        name: 'ThemeHeaderNavigation',

        components: {
            BizButton,
            BizButtonIcon,
            BizLanguageTab,
            BizTooltip,
            NavigationFormDuplicate,
            NavigationFormMenu,
            NavigationMenu,
        },

        mixins: [
            MixinHasLoader,
            MixinHasModal,
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
            let defaultLocale = usePage().props.defaultLanguage;

            return {
                baseRouteName: usePage().props.baseRouteName ?? null,
                defaultLocale,
                i18n: computed(() => usePage().props.i18n),
                iconAdd,
                localeOptions: usePage().props.languageOptions ?? [],
                selectedLocale: ref(defaultLocale),
            }
        },

        data() {
            return {
                isFormDuplicateModalOpen: false,
                lastDataMenuItems: [],
                menuForm: {},
                menuItemErrors: {},
                selectedMenuItem: {},
                validationRoute: route('admin.api.theme.header.menu-item.validate'),
            };
        },

        mounted() {
            this.setMenuForm(this.selectedLocale);
        },

        methods: {
            isFormDirty() {
                return this.menuForm.isDirty;
            },

            setMenuForm(locale) {
                this.menuForm = useForm({
                    locale: locale,
                    menu_items: cloneDeep(this.headerMenus[locale]),
                });

                this.updateLastDataMenuItems();
            },

            changeLocale(locale) {
                if (this.menuForm.isDirty) {
                    confirmLeaveProgress().then((result) => {
                        if (result.isConfirmed) {
                            this.menuForm.reset();

                            this.onChangeLocale(locale);
                        }
                    });
                } else {
                    this.onChangeLocale(locale);
                }
            },

            onChangeLocale(locale) {
                this.selectedLocale = locale;

                this.setMenuForm(locale);
            },

            openFormMenuModal(dataPosition = null) {
                this.selectedMenuItem = {};
                this.menuItemErrors = {};

                this.addMenuItemPosition = dataPosition;

                this.openModal();
            },

            openFormDuplicateModal(menuItem) {
                const cloneMenuItem = cloneDeep(menuItem);
                cloneMenuItem['id'] = null;
                cloneMenuItem['parent_id'] = null;
                cloneMenuItem['children'] = [];
                cloneMenuItem['locale'] = this.selectedLocale;

                this.selectedMenuItem = cloneMenuItem;
                this.menuItemErrors = {};

                this.isFormDuplicateModalOpen = true;
            },

            closeFormDuplicateModal() {
                this.selectedMenuItem = {};
                this.menuItemErrors = {};

                this.isFormDuplicateModalOpen = false;
            },

            async validateNestedMenuItems() {
                const self = this;

                forEach(self.menuForm.menu_items, function(menuItem) {
                    forEach(menuItem.children, function(child) {
                        if (child['children'].length > 0) {
                            self.menuForm.menu_items = self.lastDataMenuItems;
                            oopsAlert({
                                text: "Cannot add more than 2 levels of nested menus.",
                            });
                        }
                    });
                });
            },

            async checkNestedMenuItems() {
                await this.validateNestedMenuItems();

                this.updateLastDataMenuItems();
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
                        if (self.selectedParentIndex) {
                            self
                                .menuForm
                                .menu_items[self.selectedParentIndex]
                                .children
                                .push(
                                    cloneDeep(menuItem)
                                );
                        } else {
                            switch (self.addMenuItemPosition) {
                            case 'front':
                                self.menuForm.menu_items.unshift(
                                    cloneDeep(menuItem)
                                );
                                break;

                            default:
                                self.menuForm.menu_items.push(
                                    cloneDeep(menuItem)
                                );
                                break;
                            }
                        }

                        self.selectedParentIndex = null;

                        self.updateLastDataMenuItems();
                        self.closeModal();
                    })
                    .catch((error) => {
                        self.menuItemErrors = error.response.data.errors;
                    });
            },

            addChildMenuItem(parentIndex) {
                this.selectedParentIndex = parentIndex;

                this.openFormMenuModal();
            },

            editRow(menuItem) {
                this.selectedMenuItem = menuItem;
                this.menuItemErrors = {};

                this.openModal();
            },

            onSubmit() {
                const self = this;

                self.menuForm.post(
                    route(self.baseRouteName+'.navigation.update'),
                    {
                        preserveScroll: true,
                        errorBag: 'navigation',
                        onStart: () => self.onStartLoadingOverlay(),
                        onSuccess: (page) => successAlert(page.props.flash.message),
                        onFinish: () => self.onEndLoadingOverlay(),
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

                                    self.setMenuForm(menuItem['locale']);

                                    self.menuForm.menu_items.push(menuItem);

                                    self.closeFormDuplicateModal();
                                }
                            });
                        } else {
                            if (self.selectedLocale !== menuItem['locale']) {
                                self.selectedLocale = menuItem['locale'];

                                this.setMenuForm(menuItem['locale']);
                            }

                            self.menuForm.menu_items.push(menuItem);

                            self.closeFormDuplicateModal();
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
