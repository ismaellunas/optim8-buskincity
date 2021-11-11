<template>
    <draggable
        class="has-background-light"
        handle=".handle-menu"
        item-key="id"
        tag="nav"
        :animation="300"
        :class="panelClasses"
        :group="{ name: 'g1' }"
        :list="menuItems"
    >
        <template #item="{ element }">
            <div>
                <theme-menu-item
                    :menu-item="element"
                    :locale-options="localeOptions"
                    :is-child="isChild"
                    :selected-locale="selectedLocale"
                    @delete-row="deleteRow"
                    @duplicate-menu-item-above="duplicateMenuItemAbove"
                    @duplicate-menu-item-below="duplicateMenuItemBelow"
                    @duplicate-menu-item-locale="duplicateMenuItemLocale"
                    @edit-row="$emit('editRow', $event)"
                />

                <navigation-menu
                    v-if="!isChild"
                    :menu-items="element.children"
                    :locale-options="localeOptions"
                    :selected-locale="selectedLocale"
                    @duplicate-menu-item-locale="duplicateMenuItemLocale"
                    @edit-row="editRow"
                    @update-last-data-menu-items="updateLastDataMenuItems"
                />
            </div>
        </template>

        <template #footer>
            <a
                v-if="!isChild"
                class="panel-block p-4 has-background-white border-top has-text-link"
                @click.prevent="$emit('openFormModal')"
            >
                <span class="panel-icon handle-menu has-text-link">
                    <i
                        class="fas fa-plus"
                        aria-hidden="true"
                    />
                </span>
                Add new menu item
            </a>
        </template>
    </draggable>
</template>

<script>
    import Draggable from "vuedraggable";
    import ThemeMenuItem from '@/Sdb/ThemeMenuItem';
    import { usePage } from '@inertiajs/inertia-vue3';
    import { confirmDelete } from '@/Libs/alert';
    import { cloneDeep } from 'lodash';

    export default {
        name: 'NavigationMenu',

        components: {
            Draggable,
            ThemeMenuItem,
        },

        props: {
            isChild: {
                type: Boolean,
                default: true,
            },
            menuItems: {
                type: Array,
                default:() => {},
            },
            localeOptions: {
                type: Array,
                default:() => {},
            },
            selectedLocale: {
                type: String,
                default: "en"
            }
        },

        emits: [
            'duplicateMenuItemLocale',
            'editRow',
            'menuItems',
            'openFormModal',
            'updateLastDataMenuItems'
        ],

        setup() {
            return {
                baseRouteName: usePage().props.value.baseRouteName ?? null,
            };
        },

        computed: {
            panelClasses() {
                return {
                    'child-panel': this.isChild,
                    'panel': true,
                    'pl-4': true,
                };
            },
        },

        methods: {
            editRow(menuItem) {
                this.$emit('editRow', menuItem);
            },

            deleteRow(index) {
                const self = this;
                const menuItems = this.menuItems;
                let message = "";

                if (menuItems[index].children.length > 0) {
                    message = "A nested menu will deleted too."
                }

                confirmDelete("Are you sure?", message).then((result) => {
                    if (result.isConfirmed) {
                        self.$emit('menuItems', menuItems.splice(index, 1));
                        self.updateLastDataMenuItems();
                    }
                });
            },

            duplicateMenuItemAbove(menuItem, index) {
                const menuItems = this.menuItems;
                const cloneMenuItem = cloneDeep(menuItem);

                cloneMenuItem['id'] = null;
                cloneMenuItem['children'] = [];

                this.$emit('menuItems', menuItems.splice(index, 0, cloneMenuItem));
                this.updateLastDataMenuItems();
            },

            duplicateMenuItemBelow(menuItem, index) {
                const menuItems = this.menuItems;
                const cloneMenuItem = cloneDeep(menuItem);

                cloneMenuItem['id'] = null;
                cloneMenuItem['children'] = [];

                this.$emit('menuItems', menuItems.splice(index + 1, 0, cloneMenuItem));
                this.updateLastDataMenuItems();
            },

            duplicateMenuItemLocale(locale, menuItem) {
                this.$emit('duplicateMenuItemLocale', locale, menuItem);
            },

            updateLastDataMenuItems() {
                this.$emit('updateLastDataMenuItems');
            },
        },
    }
</script>

<style scoped>
    .handle-menu {
        cursor: pointer;
    }

    .panel {
        min-height: 20px;
    }

    .child-panel {
        box-shadow: none !important;
        border-radius: 0 !important;
    }

    .border-top {
        border-top: 1px solid rgb(236, 236, 236);
    }
</style>