<template>
    <div class="box panel-block has-background-white mb-0">
        <div class="level">
            <div class="level-left">
                <span class="panel-icon handle-menu">
                    <i
                        class="fas fa-bars"
                        aria-hidden="true"
                    />
                </span>
                <span
                    v-if="space?.children && space.children.length > 0"
                    class="panel-icon"
                >
                    <i
                        class="fas fa-caret-down"
                        aria-hidden="true"
                    />
                </span>

                {{ space.name }}
            </div>

            <div class="level-right">
                <biz-button
                    v-if="canAddItem"
                    class="is-ghost has-text-black"
                    type="button"
                    @click="$emit('edit-row', space)"
                >
                    <span class="icon is-small">
                        <i class="fas fa-plus" />
                    </span>
                </biz-button>
                <biz-button
                    class="is-ghost has-text-black"
                    type="button"
                    @click="$emit('edit-row', space)"
                >
                    <span class="icon is-small">
                        <i class="fas fa-pen" />
                    </span>
                </biz-button>

                <biz-button
                    class="is-ghost has-text-black ml-1"
                    type="button"
                    @click="$emit('delete-row', space)"
                >
                    <span class="icon is-small">
                        <i class="far fa-trash-alt" />
                    </span>
                </biz-button>
            </div>
        </div>
    </div>
</template>

<script>
    import BizButton from '@/Biz/Button';

    export default {
        name: 'SpaceItem',

        components: {
            BizButton,
        },

        props:{
            space: { type: Object, required: true },
            //isChild: { type: Boolean, required: false },
            canAddItem: { type: Boolean, required: true },
        },

        emits: [
            'delete-row',
            'duplicate-menu-item',
            'edit-row',
        ],
    };
</script>

<style scoped>
    .handle-menu {
        cursor: pointer;
    }

    .level {
        width: 100%;
    }
</style>
