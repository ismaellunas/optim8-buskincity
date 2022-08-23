<template>
    <section>
        <div class="columns">
            <div class="column">
                <div class="is-pulled-left">
                    <b>Menu Items</b><br>
                </div>
            </div>
            <div class="column">
                <biz-language-tab
                    class="is-pulled-right"
                    :locale-options="localeOptions"
                    :selected-locale="selectedLocale"
                    @on-change-locale="onChangeLocale"
                />
            </div>
        </div>

        <navigation-segment
            :locale-options="localeOptions"
            :menu-items="menuForm.menu_items"
            :menu="menu"
            :selected-locale="selectedLocale"
            @open-duplicate-modal="openDuplicateModal"
        />

        <div class="columns">
            <div class="column">
                <biz-button
                    class="is-primary"
                    :disabled="hasMaxSegment"
                    @click="addSegment()"
                >
                    <span class="icon">
                        <i :class="icon.add" />
                    </span>
                    &nbsp;
                    Add new segment
                </biz-button>
            </div>
        </div>

        <navigation-form-duplicate
            v-if="isModalOpen"
            :errors="menuItemErrors"
            :locale-options="localeOptions"
            :menu-item="selectedMenuItem"
            @close="closeModal()"
            @duplicate-menu-item="duplicateMenuItem"
        />
    </section>
</template>

<script>
    import MixinHasModal from '@/Mixins/HasModal';
    import BizButton from '@/Biz/Button';
    import BizLanguageTab from '@/Biz/LanguageTab';
    import icon from '@/Libs/icon-class';
    import NavigationFormDuplicate from '@/Pages/ThemeHeader/NavigationFormDuplicate';
    import NavigationSegment from './NavigationSegment';
    import { cloneDeep, isEmpty } from 'lodash';
    import { success as successAlert, confirmLeaveProgress } from '@/Libs/alert';
    import { usePage, useForm } from '@inertiajs/inertia-vue3';

    export default {
        name: 'ThemeFooterNavigation',

        components: {
            BizButton,
            BizLanguageTab,
            NavigationFormDuplicate,
            NavigationSegment,
        },

        mixins: [
            MixinHasModal,
        ],

        props: {
            menu: {
                type: Object,
                required: true,
            },
            footerMenus: {
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
                baseRouteName: usePage().props.value.baseRouteName ?? null,
                defaultLocale: usePage().props.value.defaultLanguage,
                localeOptions: usePage().props.value.languageOptions ?? [],
                tabs: {
                    layout: { title: 'Layout'},
                    navigation: {title: 'Navigation'},
                },
                icon,
            }
        },

        data() {
            return {
                activeTab: 'navigation',
                isModalDuplicateOpen: false,
                loader: null,
                menuForm: {},
                menuItemErrors: {},
                segmentIndex: null,
                selectedLocale: this.defaultLocale,
                selectedMenuItem: {},
                validationRoute: route('admin.api.theme.header.menu-item.validate'),
                emptySegment: {
                    id: null,
                    children: [],
                    is_blank: false,
                    menu_id: this.menu.id,
                    order: null,
                    page_id: null,
                    parent_id: null,
                    post_id: null,
                    title: null,
                    type: usePage().props.value.typeSegment,
                    url: null,
                }
            };
        },

        computed: {
            hasMaxSegment() {
                return this.menuForm.menu_items?.length >= 4;
            },
        },

        mounted() {
            this.menuForm = this.getMenuForm(this.selectedLocale);
        },

        methods: {
            isFormDirty() {
                return this.menuForm.isDirty;
            },

            getMenuForm(locale) {
                const emptySegment = [cloneDeep(this.emptySegment)];

                return useForm({
                    locale: locale,
                    menu_items: cloneDeep(
                        !isEmpty(this.footerMenus)
                            ? (
                                !isEmpty(this.footerMenus[locale])
                                    ? this.footerMenus[locale]
                                    : emptySegment
                            )
                            : emptySegment
                    ),
                });
            },

            onChangeLocale(locale) {
                if (this.menuForm.isDirty) {
                    confirmLeaveProgress().then((result) => {
                        if (result.isDismissed) {
                            return false;
                        } else if(result.isConfirmed) {
                            this.selectedLocale = locale;
                            this.menuForm.reset();
                            this.menuForm = this.getMenuForm(locale);
                        }
                    });
                } else {
                    this.selectedLocale = locale;

                    this.menuForm = this.getMenuForm(locale);
                }
            },

            addSegment() {
                if (!this.hasMaxSegment) {
                    this.menuForm.menu_items.push(
                        cloneDeep(this.emptySegment)
                    );
                }
            },

            openDuplicateModal(menuItem, segmentIndex) {
                this.selectedMenuItem = menuItem;
                this.segmentIndex = segmentIndex;
                this.menuItemErrors = {};

                this.openModal();
            },

            // Override from hasModal mixin
            onCloseModal() {
                this.selectedMenuItem = {};
                this.menuItemErrors = {};
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
                                    self.menuForm.menu_items[0].children.push(menuItem);

                                    self.closeModal();
                                }
                            });
                        } else {
                            if (self.selectedLocale !== menuItem['locale']) {
                                self.selectedLocale = menuItem['locale'];
                                self.menuForm = self.getMenuForm(menuItem['locale']);

                                self.menuForm.menu_items[0].children.push(menuItem);
                            } else {
                                self.menuForm.menu_items[self.segmentIndex].children.push(menuItem);
                            }


                            self.closeModal();
                        }
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
                this.selectedMenuItem['page_id'] = menuItem['page_id'];
                this.selectedMenuItem['post_id'] = menuItem['post_id'];
                this.selectedMenuItem['category_id'] = menuItem['category_id'];
            },

            updateMenuItems() {
                const self = this;
                this.menuForm.post(route(this.baseRouteName+'.update-menu-item'), {
                    preserveScroll: true,
                    onStart: visit => {
                        self.loader = self.$loading.show();
                    },
                    onSuccess: (page) => {
                        successAlert(page.props.flash.message);
                    },
                    onFinish: visit => {
                        self.loader.hide();
                    },
                });
            },
        }
    };
</script>
