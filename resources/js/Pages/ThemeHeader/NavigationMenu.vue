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
                                @click="$emit('deleteRow', element.id)"
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
                    @delete-row="deleteRow"
                    @edit-row="editRow"
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
    import SdbButton from '@/Sdb/Button';
    import SdbTag from '@/Sdb/Tag';

    export default {
        name: 'NavigationMenu',

        components: {
            Draggable,
            SdbButton,
            SdbTag,
        },

        props: {
            isChild: {
                type: Boolean,
                default: true,
            },
            items: {
                type: Array,
                default() {
                    return [];
                },
            },
        },

        emits: [
            'deleteRow',
            'editRow',
            'openFormModal',
        ],

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