<template>
    <draggable
        class="has-background-light"
        handle=".handle-menu"
        item-key="id"
        tag="nav"
        :animation="300"
        :class="panelClass"
        :group="{ name: 'g1' }"
        :list="items"
    >
        <template #item="{ element, index }">
            <div>
                <div
                    class="panel-block p-4 has-background-white"
                >
                    <div class="level">
                        <div
                            class="level-left"
                            :class="isChild ? 'pl-4' : ''"
                        >
                            <span class="panel-icon handle-menu">
                                <i
                                    class="fas fa-bars"
                                    aria-hidden="true"
                                />
                            </span>
                            <span
                                v-if="element.children.length > 0"
                                class="panel-icon"
                            >
                                <i
                                    class="fas fa-caret-down"
                                    aria-hidden="true"
                                />
                            </span>
                            {{ element.title }}
                            <sdb-tag
                                v-for="translation in element.translations"
                                :key="translation.id"
                                class="is-info px-2 ml-1 is-small"
                            >
                                {{ translation.locale?.toUpperCase() }}
                            </sdb-tag>
                        </div>
                        <div class="level-right">
                            <sdb-dropdown
                                style-button="border: none"
                            >
                                <template #trigger>
                                    <i
                                        class="far fa-copy"
                                    />
                                </template>
                                <template #default>
                                    <template
                                        v-for="localeOption in localeOptions"
                                        :key="localeOption.id"
                                    >
                                        <a
                                            v-if="localeOption.id != selectedLocale"
                                            class="dropdown-item"
                                            @click.prevent="duplicateMenuItemLocale(localeOption.id, element)"
                                        >
                                            Duplicate to {{ localeOption.name }}
                                        </a>
                                    </template>
                                    <a
                                        class="dropdown-item"
                                        @click.prevent="duplicateMenuItemAbove(element, index)"
                                    >
                                        Duplicate Menu Above
                                    </a>
                                    <a
                                        class="dropdown-item"
                                        @click.prevent="duplicateMenuItemBelow(element, index)"
                                    >
                                        Duplicate Menu Below
                                    </a>
                                </template>
                            </sdb-dropdown>
                            <sdb-button
                                type="button"
                                class="is-ghost has-text-black"
                                @click="$emit('editRow', element)"
                            >
                                <span class="icon is-small">
                                    <i class="fas fa-pen" />
                                </span>
                            </sdb-button>
                            <sdb-button
                                type="button"
                                class="is-ghost has-text-black ml-1"
                                @click="deleteConfirm(index)"
                            >
                                <span class="icon is-small">
                                    <i class="far fa-trash-alt" />
                                </span>
                            </sdb-button>
                        </div>
                    </div>
                </div>
                <navigation-menu
                    v-if="!isChild"
                    :items="element.children"
                    :locale-options="localeOptions"
                    :selected-locale="selectedLocale"
                    @duplicate-menu-item-locale="duplicateMenuItemLocale"
                    @edit-row="editRow"
                    @update-last-data-menu-item="updateLastDataMenuItem"
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
    import axios from 'axios';
    import Draggable from "vuedraggable";
    import SdbButton from '@/Sdb/Button';
    import SdbDropdown from '@/Sdb/Dropdown';
    import SdbTag from '@/Sdb/Tag';
    import { usePage } from '@inertiajs/inertia-vue3';
    import { confirmDelete } from '@/Libs/alert';
    import { forEach, cloneDeep } from 'lodash';

    export default {
        name: 'NavigationMenu',

        components: {
            Draggable,
            SdbButton,
            SdbDropdown,
            SdbTag,
        },

        props: {
            isChild: {
                type: Boolean,
                default: true,
            },
            items: {
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
            'deleteRow',
            'duplicateMenuItemLocale',
            'editRow',
            'items',
            'openFormModal',
            'updateLastDataMenuItem'
        ],

        setup() {
            return {
                baseRouteName: usePage().props.value.baseRouteName ?? null,
            };
        },

        computed: {
            panelClass() {
                if (this.isChild) {
                    return ['panel', 'childPanel'];
                } else {
                    return ['panel'];
                }
            },
        },

        methods: {
            editRow(menuItem) {
                this.$emit('editRow', menuItem);
            },

            deleteConfirm(index) {
                const self = this;
                const items = this.items;
                let message = "";

                if (items[index].children.length > 0) {
                    message = "A nested menu will deleted too."
                }

                confirmDelete("Are you sure?", message).then((result) => {
                    if (result.isConfirmed) {
                        self.deleteRow(items[index]);

                        /*
                        forEach(items[index].children, function(item) {
                            self.deleteRow(item);
                        });
                        */

                        self.$emit('items', items.splice(index, 1));
                        self.updateLastDataMenuItem();
                    }
                });
            },

            deleteRow(items) {
                if (items.id !== null) {
                    axios.delete(
                        route(this.baseRouteName+'.destroy', items.id)
                    );
                }
            },

            duplicateMenuItemAbove(menuItem, index) {
                const items = this.items;
                const cloneMenuItem = cloneDeep(menuItem);

                cloneMenuItem['id'] = null;
                cloneMenuItem['children'] = [];

                this.$emit('items', items.splice(index, 0, cloneMenuItem));
                this.updateLastDataMenuItem();
            },

            duplicateMenuItemBelow(menuItem, index) {
                const items = this.items;
                const cloneMenuItem = cloneDeep(menuItem);

                cloneMenuItem['id'] = null;
                cloneMenuItem['children'] = [];

                this.$emit('items', items.splice(index + 1, 0, cloneMenuItem));
                this.updateLastDataMenuItem();
            },

            duplicateMenuItemLocale(locale, menuItem) {
                this.$emit('duplicateMenuItemLocale', locale, menuItem);
            },

            updateLastDataMenuItem() {
                this.$emit('updateLastDataMenuItem');
            },
        },
    }
</script>

<style scoped>
    .handle-menu {
        cursor: pointer;
    }

    .level {
        width: 100%;
    }

    .panel {
        min-height: 20px;
    }

    .childPanel {
        box-shadow: none !important;
        border-radius: 0 !important;
    }

    .border-top {
        border-top: 1px solid rgb(236, 236, 236);
    }
</style>