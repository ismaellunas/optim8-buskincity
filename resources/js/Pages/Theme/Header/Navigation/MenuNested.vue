<template>
    <draggable
        class="panel"
        tag="nav"
        handle=".handle-menu"
        :animation="300"
        :list="items"
        item-key="id"
    >
        <template #item="{ element }">
            <div class="panel-block p-4">
                <div class="level">
                    <div class="level-left">
                        <span class="panel-icon handle-menu">
                            <i class="fas fa-bars" aria-hidden="true"></i>
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
            'editRow'
        ],

        props: {
            items: {
                type: Array,
                default: [],
            }
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
</style>