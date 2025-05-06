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
            </div>
        </template>

        <template #header>
            <slot name="header" />
        </template>

        <template #footer>
            <a
                v-if="!isChild"
                class="panel-block p-4 has-background-white border-top has-text-link"
                @click.prevent="$emit('open-form-modal', segmentIndex)"
            >
                <span class="panel-icon handle-menu has-text-link">
                    <i
                        :class="icon.add"
                        aria-hidden="true"
                    />
                </span>
                {{ sentenceCase(i18n.add_menu_item) }}
            </a>
        </template>
    </draggable>
</template>

<script>
    import Draggable from "vuedraggable";
    import ThemeMenuItem from '@/Biz/ThemeMenuItem.vue';
    import icon from '@/Libs/icon-class';
    import { usePage } from '@inertiajs/vue3';
    import { confirmDelete } from '@/Libs/alert';
    import { sentenceCase } from 'change-case';

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
            },
            segmentIndex: {
                type: Number,
                required: true,
            }
        },

        emits: [
            'duplicate-menu-item',
            'edit-row',
            'menu-items',
            'open-form-modal',
            'update-last-data-menu-items'
        ],

        setup() {
            return {
                baseRouteName: usePage().props.baseRouteName ?? null,
            };
        },

        data() {
            return {
                icon
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
                this.$emit('duplicate-menu-item', menuItem, this.segmentIndex);
            },

            updateLastDataMenuItems() {
                this.$emit('update-last-data-menu-items');
            },

            sentenceCase,
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