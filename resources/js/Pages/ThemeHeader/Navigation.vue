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
                    @change="checkNestedMenuItems"
                    @duplicate-menu-item-locale="duplicateMenuItemLocale"
                    @edit-row="editRow"
                    @open-form-modal="openFormModal()"
                    @update-last-data-menu-item="updateLastDataMenuItem"
                />
            </div>
        </div>

        <navigation-form-menu
            v-if="isModalOpen"
            :base-route-name="baseRouteName"
            :menu="menu"
            :menu-item="selectedMenuItems"
            :selected-locale="selectedLocale"
            @add-menu-item="addMenuItem"
            @close="closeModal()"
            @update-last-data-menu-item="updateLastDataMenuItem"
        />
    </section>
</template>

<script>
    import MixinHasModal from '@/Mixins/HasModal';
    import NavigationFormMenu from './NavigationFormMenuItem';
    import NavigationMenu from './NavigationMenu';
    import SdbButton from '@/Sdb/Button';
    import { usePage, useForm } from '@inertiajs/inertia-vue3';
    import { oops as oopsAlert, success as successAlert  } from '@/Libs/alert';
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

            updateLastDataMenuItem() {
                const self = this;
                forEach(self.localeOptions, function(value) {
                    self.lastMenuItems[value.id] = cloneDeep(self.items[value.id]);
                })
            },

            syncMenuItems() {
                this.items = useForm(this.menuItems);
                this.updateLastDataMenuItem();
            },

            addMenuItem(menuItem) {
                this.items[menuItem.locale].push(
                    cloneDeep(menuItem)
                );

                this.updateLastDataMenuItem();
            },

            editRow(menuItem) {
                this.selectedMenuItems = menuItem;
                this.openModal();
            },

            updateMenuItems() {
                this.items.post(route(this.baseRouteName+'.update-menu-item'), {
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
                cloneMenuItem['locale'] = locale;
                cloneMenuItem['parent_id'] = null;
                cloneMenuItem['children'] = [];

                this.items[locale].push(
                    cloneDeep(cloneMenuItem)
                );

                this.changeLocale(locale);
                this.updateLastDataMenuItem();
            },
        }
    }
</script>