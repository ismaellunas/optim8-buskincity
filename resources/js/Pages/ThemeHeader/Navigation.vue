<template>
    <section>
        <div class="columns">
            <div class="column">
                <div class="is-pulled-left">
                    <b>Menu Items</b><br>
                    Last Saved: {{ lastSaved }}
                </div>
            </div>
            <div class="column">
                <p class="buttons is-pulled-right">
                    <sdb-button
                        v-for="locale in localeOptions"
                        :key="locale.id"
                        :class="['is-small is-link is-rounded', locale.id == selectedLocale ? '' : 'is-light' ]"
                        @click="changeLocale(locale.id)"
                    >
                        {{ locale.name }}
                    </sdb-button>
                </p>
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
                    @duplicate-menu-item-locale="duplicateMenuItemLocale"
                    @edit-row="editRow"
                    @open-form-modal="openFormModal()"
                    @update-last-data-menu-items="updateLastDataMenuItems"
                />
            </div>
        </div>

        <navigation-form-menu
            v-if="isModalOpen"
            :base-route-name="baseRouteName"
            :menu="menu"
            :menu-item="selectedMenuItem"
            :selected-locale="selectedLocale"
            @add-menu-item="addMenuItem"
            @close="closeModal()"
            @update-last-data-menu-items="updateLastDataMenuItems"
        />
    </section>
</template>

<script>
    import MixinHasModal from '@/Mixins/HasModal';
    import NavigationFormMenu from './NavigationFormMenuItem';
    import NavigationMenu from './NavigationMenu';
    import SdbButton from '@/Sdb/Button';
    import { usePage, useForm } from '@inertiajs/inertia-vue3';
    import { oops as oopsAlert, success as successAlert, confirm as confirmAlert } from '@/Libs/alert';
    import { forEach, cloneDeep } from 'lodash';

    export default {
        name: 'Navigation',

        components: {
            NavigationFormMenu,
            NavigationMenu,
            SdbButton,
        },

        mixins: [
            MixinHasModal,
        ],

        props: {
            menu: {
                type: Object,
                required: true,
            },
            lastSaved: {
                type: String,
                default: "-",
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
                baseRouteName: usePage().props.value.baseRouteName ?? null,
                localeOptions: usePage().props.value.languageOptions ?? [],
                defaultLocale: usePage().props.value.defaultLanguage,
                tabs: {
                    layout: { title: 'Layout'},
                    navigation: {title: 'Navigation'},
                },
            }
        },

        data() {
            return {
                activeTab: 'navigation',
                lastDataMenuItems: [],
                loader: null,
                menuForm: {},
                selectedLocale: this.defaultLocale,
                selectedMenuItem: {},
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

            changeLocale(locale) {
                if (this.menuForm.isDirty) {
                    const confirmationMessage = (
                        'It looks like you have been editing something. '
                        + 'If you leave before saving, your changes will be lost.'
                    );

                    confirmAlert('Are you sure?', confirmationMessage, 'Leave this', {
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

            openFormModal() {
                this.selectedMenuItem = {};
                this.isModalOpen = true;
            },

            checkNestedMenuItems() {
                let self = this;
                forEach(self.items[self.selectedLocale], function(values) {
                    forEach(values.children, function(value) {
                        if (value['children'].length > 0) {
                            self.items[self.selectedLocale] = self.lastMenuItems[self.selectedLocale];
                            oopsAlert(null, "Cannot add nested menu more than 2");
                        }
                    });
                });

                self.updateLastDataMenuItem();
            },

            updateLastDataMenuItems() {
                this.lastDataMenuItems = cloneDeep(this.menuForm.menu_items);
            },

            addMenuItem(menuItem) {
                this.menuForm.menu_items.push(
                    cloneDeep(menuItem)
                );

                this.updateLastDataMenuItems();
            },

            editRow(menuItem) {
                this.selectedMenuItem = menuItem;
                this.openModal();
            },

            updateMenuItems() {
                // this.items.post(route(this.baseRouteName+'.update-menu-item'), {
                //     preserveScroll: true,
                //     onSuccess: (page) => {
                //         successAlert(page.props.flash.message);
                //         this.syncMenuItems();
                //     },
                //     onError: () => {
                //         this.items = useForm(this.lastMenuItems);
                //     }
                // });

                this.menuForm.post(route(this.baseRouteName+'.update-menu-item'), {
                    preserveScroll: true,
                    onSuccess: (page) => {
                        successAlert(page.props.flash.message);
                        this.syncMenuItems();
                    },
                    onError: () => {
                        this.items = useForm(this.lastMenuItems);
                    }
                });
            },

            duplicateMenuItemLocale(locale, menuItem) {
                const cloneMenuItem = cloneDeep(menuItem);
                cloneMenuItem['id'] = null;
                cloneMenuItem['parent_id'] = null;
                cloneMenuItem['children'] = [];

                this.items[locale].push(cloneMenuItem);

                this.changeLocale(locale);
                this.updateLastDataMenuItems();
            },
        }
    }
</script>
