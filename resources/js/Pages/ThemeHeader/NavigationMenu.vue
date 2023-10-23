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
                    :is-up-button-disabled="index == 0"
                    :is-down-button-disabled="index == (menuItems.length - 1)"
                    @delete-row="deleteRow"
                    @duplicate-menu-item="duplicateMenuItem"
                    @edit-row="$emit('edit-row', $event)"
                    @move-menu-item="moveMenuItem"
                    @add-child-menu-item="$emit('add-child-menu-item', $event)"
                />

                <navigation-menu
                    v-if="! isChild"
                    :menu-items="element.children"
                    :locale-options="localeOptions"
                    :selected-locale="selectedLocale"
                    @change="$emit('check-nested-menu-items')"
                    @check-nested-menu-items="$emit('check-nested-menu-items')"
                    @duplicate-menu-item="duplicateMenuItem"
                    @edit-row="editRow"
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
    import { useModelWrapper } from '@/Libs/utils';
    import { computed } from 'vue';

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
            'add-child-menu-item',
            'check-nested-menu-items',
            'duplicate-menu-item',
            'edit-row',
            'menu-items',
        ],

        setup(props, {emit}) {
            return {
                baseRouteName: usePage().props.baseRouteName ?? null,
                computedMenuItems: useModelWrapper(props, emit, 'menuItems'),
                i18n: computed(() => usePage().props.i18n),
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
                    'draggable-area': this.isChild,
                };
            },

            areaStyles() {
                if (! this.isChild) {
                    return {};
                }

                return {
                    'min-height': '40px',
                };
            },
        },

        methods: {
            editRow(menuItem) {
                this.$emit('edit-row', menuItem);
            },

            deleteRow(index) {
                const self = this;
                const menuItems = self.menuItems;
                let message = "";

                if (menuItems[index].children.length > 0) {
                    message = self.i18n.menu_items_delete;
                }

                confirmDelete(self.i18n.are_you_sure, message).then((result) => {
                    if (result.isConfirmed) {
                        self.$emit('menu-items', menuItems.splice(index, 1));

                        self.$emit('check-nested-menu-items');
                    }
                });
            },

            duplicateMenuItem(menuItem) {
                this.$emit('duplicate-menu-item', menuItem);
            },

            moveMenuItem(type, index) {
                switch (type) {
                case 'up':
                    this.moveMenuItemUp(index);
                    break;

                case 'down':
                    this.moveMenuItemDown(index);
                    break;
                }

                this.$emit('check-nested-menu-items');
            },

            moveMenuItemUp(index) {
                if (index > 0 && index < this.computedMenuItems.length) {
                    const item = this.computedMenuItems[index];
                    this.computedMenuItems.splice(index, 1);
                    this.computedMenuItems.splice(index - 1, 0, item);
                }
            },

            moveMenuItemDown(index) {
                if (index >= 0 && index < this.computedMenuItems.length - 1) {
                    const item = this.computedMenuItems[index];
                    this.computedMenuItems.splice(index, 1);
                    this.computedMenuItems.splice(index + 1, 0, item);
                }
            },
        },
    }
</script>