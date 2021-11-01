<template>
    <draggable
        class="has-background-light"
        :class="panelClass"
        tag="nav"
        handle=".handle-menu"
        :animation="300"
        :list="items"
        :group="{ name: 'g1' }"
        item-key="id"
    >
        <template #item="{ element }">
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
                                <i class="fas fa-bars" aria-hidden="true"></i>
                            </span>
                            <span
                                v-if="element.children.length > 0"
                                class="panel-icon"
                            >
                                <i class="fas fa-caret-down" aria-hidden="true"></i>
                            </span>
                            {{ element.title }}
                            <sdb-tag
                                v-for="translation in element.translations"
                                :key="translation.id"
                                class="is-info px-2 ml-1 is-small">
                                {{ translation.locale?.toUpperCase() }}
                            </sdb-tag>
                        </div>
                        <div class="level-right">
                            <!-- <sdb-dropdown
                                :active="false"
                            >
                                <template v-slot:trigger>
                                    <span class="icon is-small">
                                        <i class="far fa-copy"></i>
                                    </span>
                                </template>
                                <template v-slot:default>
                                    <a
                                        href="#"
                                        class="dropdown-item"
                                        @click.prevent="$emit('duplicateMenuAbove', element)"
                                    >
                                        Duplicate Menu Above
                                    </a>
                                    <a
                                        href="#"
                                        class="dropdown-item"
                                        @click.prevent="$emit('duplicateMenuBelow', element)"
                                    >
                                        Duplicate Menu Below
                                    </a>
                                </template>
                            </sdb-dropdown> -->
                            <sdb-button
                                type="button"
                                class="is-ghost has-text-black"
                                @click="$emit('editRow', element)"
                            >
                                <span class="icon is-small">
                                    <i class="fas fa-pen"></i>
                                </span>
                            </sdb-button>
                            <sdb-button
                                type="button"
                                class="is-ghost has-text-black ml-1"
                                @click="$emit('deleteRow', element.id)"
                            >
                                <span class="icon is-small">
                                    <i class="far fa-trash-alt"></i>
                                </span>
                            </sdb-button>
                        </div>
                    </div>
                </div>
                <menu-nested
                    v-if="!isChild"
                    :items="element.children"
                    @edit-row="editRow"
                    @delete-row="deleteRow"
                ></menu-nested>
            </div>
        </template>
        <template #footer>
            <a
                v-if="!isChild"
                class="panel-block p-4 has-background-white border-top has-text-link"
                @click.prevent="$emit('openFormModal')"
            >
                <span class="panel-icon handle-menu has-text-link">
                    <i class="fas fa-plus" aria-hidden="true"></i>
                </span>
                Add new menu item
            </a>
        </template>
    </draggable>
</template>

<script>
    import draggable from "vuedraggable";
    import SdbButton from '@/Sdb/Button';
    import SdbButtonLink from '@/Sdb/ButtonLink';
    import SdbDropdown from '@/Sdb/Dropdown';
    import SdbTag from '@/Sdb/Tag';

    export default {
        name: 'MenuNested',

        components: {
            draggable,
            SdbButton,
            SdbButtonLink,
            SdbDropdown,
            SdbTag,
        },

        emits: [
            'deleteRow',
            'editRow',
            'openFormModal',
            'duplicateMenuAbove',
            'duplicateMenuBelow',
        ],

        props: {
            items: {
                type: Array,
                default: [],
            },
            isChild: {
                type: Boolean,
                default: true,
            },
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

            deleteRow(id) {
                this.$emit('deleteRow', id);
            },

            duplicateMenuAbove(menuItem) {
                this.$emit('duplicateMenuAbove', menuItem);
            },

            duplicateMenuBelow(menuItem) {
                this.$emit('duplicateMenuBelow', menuItem);
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