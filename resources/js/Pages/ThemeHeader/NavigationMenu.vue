<template>
    <draggable
        class="has-background-light"
        handle=".handle-menu"
        item-key="id"
        tag="nav"
        :animation="300"
        :class="areaClasses"
        :style="areaStyles"
        :group="{ name: 'g1' }"
        :list="menuItems"
    >
        <template #item="{ element, index }">
            <div>
                <theme-menu-item
                    :is-child="isChild"
                    :locale-options="localeOptions"
                    :menu-item-index="index"
                    :menu-item="element"
                    :selected-locale="selectedLocale"
                    @delete-row="deleteRow"
                    @duplicate-menu-item="duplicateMenuItem"
                    @edit-row="$emit('edit-row', $event)"
                />

                <navigation-menu
                    v-if="!isChild"
                    :menu-items="element.children"
                    :locale-options="localeOptions"
                    :selected-locale="selectedLocale"
                    @duplicate-menu-item="duplicateMenuItem"
                    @edit-row="editRow"
                    @update-last-data-menu-items="updateLastDataMenuItems"
                />
            </div>
        </template>
    </draggable>
</template>

<script>
    import Draggable from "vuedraggable";
    import ThemeMenuItem from '@/Biz/ThemeMenuItem.vue';
    import icon from '@/Libs/icon-class';
    import { usePage } from '@inertiajs/vue3';
    import { confirmDelete } from '@/Libs/alert';

    export default {
        name: 'NavigationMenu',

        components: {
            Draggable,
            ThemeMenuItem,
        },

        inject: {
            i18n: { default: () => ({
                add_menu_item : 'Add new menu item',
            }) }
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
            'duplicate-menu-item',
            'edit-row',
            'menu-items',
            'update-last-data-menu-items'
        ],

        setup() {
            return {
                baseRouteName: usePage().props.baseRouteName ?? null,
            };
        },

        data() {
            return {
                icon,
            };
        },

        computed: {
            areaClasses() {
                return {
                    'p-2': true,
                    'is-rounded': true,
                    'ml-5': this.isChild,
                    'mb-2': this.isChild,
                };
            },

            areaStyles() {
                if (! this.isChild) {
                    return {};
                }

                return {
                    'min-height': '40px',
                    'border': '1px solid #ccc',
                    'border-style': 'dashed',
                };
            },
        },

        methods: {
            editRow(menuItem) {
                this.$emit('edit-row', menuItem);
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
                        self.$emit('menu-items', menuItems.splice(index, 1));
                        self.updateLastDataMenuItems();
                    }
                });
            },

            duplicateMenuItem(menuItem) {
                this.$emit('duplicate-menu-item', menuItem);
            },

            updateLastDataMenuItems() {
                this.$emit('update-last-data-menu-items');
            },
        },
    }
</script>