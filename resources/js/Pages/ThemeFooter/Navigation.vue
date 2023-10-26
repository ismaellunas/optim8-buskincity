<template>
    <form @submit.prevent="onSubmit">
        <div class="columns">
            <div class="column">
                <span class="has-text-weight-bold">
                    {{ i18n.menu_items }}
                </span>

                <biz-tooltip
                    class="ml-1"
                    :message="i18n.tips.menu_items"
                />
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

        <div class="field">
            <div class="control">
                <biz-button
                    type="button"
                    class="is-primary"
                    :disabled="hasMaxSegment"
                    @click="addSegment('front')"
                >
                    <span class="icon">
                        <i :class="icon.add" />
                    </span>

                    <span class="ml-2">
                        {{ sentenceCase(i18n.add_new_segment) }}
                    </span>
                </biz-button>
            </div>
        </div>

        <navigation-segment
            :locale-options="localeOptions"
            :menu-items="menuForm.menu_items"
            :menu="menu"
            :selected-locale="selectedLocale"
            @open-duplicate-modal="openDuplicateModal"
        />

        <div class="field is-grouped is-justify-content-space-between mt-4">
            <div class="control">
                <biz-button
                    type="button"
                    class="is-primary"
                    :disabled="hasMaxSegment"
                    @click="addSegment()"
                >
                    <span class="icon">
                        <i :class="icon.add" />
                    </span>

                    <span class="ml-2">
                        {{ sentenceCase(i18n.add_new_segment) }}
                    </span>
                </biz-button>
            </div>

            <div class="control">
                <biz-button class="is-primary">
                    <span>
                        {{ i18n.save }}
                    </span>
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
    </form>
</template>

<script>
    import MixinHasModal from '@/Mixins/HasModal';
    import MixinHasLoader from '@/Mixins/HasLoader';
    import MixinHasTranslation from '@/Mixins/HasTranslation';
    import BizButton from '@/Biz/Button.vue';
    import BizLanguageTab from '@/Biz/LanguageTab.vue';
    import BizTooltip from '@/Biz/Tooltip.vue';
    import icon from '@/Libs/icon-class';
    import NavigationFormDuplicate from '@/Pages/ThemeHeader/NavigationFormDuplicate.vue';
    import NavigationSegment from './NavigationSegment.vue';
    import { cloneDeep, isEmpty } from 'lodash';
    import { success as successAlert, confirmLeaveProgress } from '@/Libs/alert';
    import { usePage, useForm } from '@inertiajs/vue3';
    import { sentenceCase } from 'change-case';

    export default {
        name: 'ThemeFooterNavigation',

        components: {
            BizButton,
            BizLanguageTab,
            BizTooltip,
            NavigationFormDuplicate,
            NavigationSegment,
        },

        mixins: [
            MixinHasModal,
            MixinHasLoader,
            MixinHasTranslation,
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
                baseRouteName: usePage().props.baseRouteName ?? null,
                defaultLocale: usePage().props.defaultLanguage,
                localeOptions: usePage().props.languageOptions ?? [],
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
                    title: null,
                    type: usePage().props.typeSegment,
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

            addSegment(type = null) {
                const self = this;

                if (! self.hasMaxSegment) {
                    switch (type) {
                    case 'front':
                        self.menuForm.menu_items.unshift(
                            cloneDeep(self.emptySegment)
                        );
                        break;

                    default:
                        self.menuForm.menu_items.push(
                            cloneDeep(self.emptySegment)
                        );
                        break;
                    }
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
                this.selectedMenuItem['menu_itemable_id'] = menuItem['menu_itemable_id'];
            },

            onSubmit() {
                const self = this;

                this.menuForm.post(route(this.baseRouteName+'.navigation.update'), {
                    preserveScroll: true,
                    onStart: () => self.onStartLoadingOverlay(),
                    onSuccess: (page) => successAlert(page.props.flash.message),
                    onFinish: () => self.onEndLoadingOverlay(),
                });
            },

            sentenceCase,
        }
    };
</script>
