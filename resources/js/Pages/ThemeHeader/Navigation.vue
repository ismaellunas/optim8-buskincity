<template>
    <section>
        <div class="columns">
            <div class="column">
                <div class="is-pulled-left">
                    <b>Menu Items</b><br>
                    Last Saved: {{ lastSaved }}
                </div>
            </div>
        </div>
        <div class="columns">
            <div class="column">
                <navigation-menu
                    :items="items"
                    :is-child="false"
                    @change="checkNestedMenu"
                    @delete-row="deleteRow"
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
            @close="closeModal()"
            @sync-menu-items="syncMenuItems()"
        />
    </section>
</template>

<script>
    import NavigationMenu from './NavigationMenu';
    import MixinHasModal from '@/Mixins/HasModal';
    import NavigationFormMenu from './NavigationFormMenuItem';
    import { useForm, usePage } from '@inertiajs/inertia-vue3';
    import { confirmDelete, oops as oopsAlert, success as successAlert  } from '@/Libs/alert';
    import { merge, forEach, cloneDeep } from 'lodash';

    export default {
        name: 'Navigation',

        components: {
            NavigationFormMenu,
            NavigationMenu,
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
                type: Array,
                default() {
                    return [];
                },
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
                baseRouteName: usePage().props.value.baseRouteName ?? null,
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
                this.$inertia.post(route(this.baseRouteName+'.update-format'), this.items, {
                    onSuccess: (page) => {
                        successAlert(page.props.flash.message);
                        this.syncMenuItems();
                    },
                });
            },
        }
    }
</script>