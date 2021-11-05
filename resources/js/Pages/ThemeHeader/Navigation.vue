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
                    :items="items[selectedLocale]"
                    :locale-options="localeOptions"
                    :selected-locale="selectedLocale"
                    @change="checkNestedMenu"
                    @delete-row="deleteRow"
                    @duplicate-menu="duplicateMenu"
                    @duplicate-menu-locale="duplicateMenuLocale"
                    @edit-row="editRow"
                    @open-form-modal="openFormModal()"
                />
            </div>
        </div>

        <navigation-form-menu
            v-if="isModalOpen"
            :base-route-name="baseRouteName"
            :menu="menu"
            :menu-item="selectedMenuItems"
            :selected-locale="selectedLocale"
            @close="closeModal()"
            @sync-menu-items="syncMenuItems()"
        />
    </section>
</template>

<script>
    import MixinHasModal from '@/Mixins/HasModal';
    import NavigationFormMenu from './NavigationFormMenuItem';
    import NavigationMenu from './NavigationMenu';
    import SdbButton from '@/Sdb/Button';
    import { usePage } from '@inertiajs/inertia-vue3';
    import { confirmDelete, oops as oopsAlert, success as successAlert  } from '@/Libs/alert';
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
            menuItems: {
                type: Object,
                default() {
                    return {};
                },
            },
            title: {
                type: String,
                default: "-"
            },
        },

        setup(props) {
            return {
                baseRouteName: usePage().props.value.baseRouteName ?? null,
                localeOptions: usePage().props.value.languageOptions ?? [],
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
                loader: null,
                selectedLocale: 'en',
                selectedMenuItems: {},
            };
        },

        mounted() {
            this.syncMenuItems();
        },

        methods: {
            changeLocale(locale) {
                this.selectedLocale = locale;
            },

            openFormModal() {
                this.selectedMenuItems = {};
                this.isModalOpen = true;
            },

            checkNestedMenu() {
                let self = this;
                forEach(self.items[self.selectedLocale], function(values) {
                    forEach(values.children, function(value) {
                        if (value['children'].length > 0) {
                            self.items[self.selectedLocale] = self.lastMenuItems[self.selectedLocale];
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
                this.$inertia.post(route(this.baseRouteName+'.update-format'), this.items, {
                    onSuccess: (page) => {
                        successAlert(page.props.flash.message);
                    },
                    onFinish: () => {
                        this.syncMenuItems();
                    }
                });
            },

            duplicateMenu(menuItem, type) {
                menuItem['id'] = null;

                this.$inertia.post(route(this.baseRouteName+'.duplicate', type), menuItem, {
                    preserveState: true,
                    onSuccess: (page) => {
                        successAlert(page.props.flash.message);
                    },
                    onFinish: () => {
                        this.syncMenuItems();
                    }
                });
            },

            duplicateMenuLocale(locale, menuItem) {
                menuItem['id'] = null;
                menuItem['locale'] = locale;
                menuItem['parent_id'] = null;

                this.$inertia.post(route(this.baseRouteName+'.store'), menuItem, {
                    preserveState: true,
                    onSuccess: () => {
                        successAlert("Menu item duplicate successfully!");
                    },
                    onFinish: () => {
                        this.syncMenuItems();
                        this.selectedLocale = locale;
                    }
                });
            }
        }
    }
</script>