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
                        </div>
                        <div class="level-right">
                            <sdb-button
                                type="button"
                                class="is-ghost has-text-black"
                                @click="$emit('editRow', element.id)"
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
    </draggable>
</template>

<script>
    import draggable from "vuedraggable";
    import SdbButton from '@/Sdb/Button';
    import SdbButtonLink from '@/Sdb/ButtonLink';

    export default {
        name: 'MenuNested',

        components: {
            draggable,
            SdbButton,
            SdbButtonLink,
        },

        emits: [
            'deleteRow',
            'editRow',
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
            editRow(id) {
                this.$emit('editRow', id);
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
</style>