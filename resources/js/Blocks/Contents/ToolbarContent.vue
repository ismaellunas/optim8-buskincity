<template>
    <div class="edit-mode-toolbar-content">
        <div class="field has-addons is-pulled-right">
            <p
                v-if="canDuplicate"
                class="control"
            >
                <biz-button-icon
                    icon-class="is-small"
                    type="button"
                    :icon="iconCopy"
                    @click="$emit('duplicate-content', $event)"
                />
            </p>
            <p
                v-if="canDelete"
                class="control"
            >
                <biz-button-icon
                    icon-class="is-small"
                    type="button"
                    :icon="iconRemove"
                    @click="$emit('delete-content', $event)"
                />
            </p>
            <p
                v-if="canMove"
                class="control"
            >
                <biz-button-icon
                    icon-class="is-small handle-content"
                    type="button"
                    :icon="iconMove"
                />
            </p>
        </div>
    </div>
</template>

<script>
    import BizButtonIcon from '@/Biz/ButtonIcon.vue';
    import { copy as iconCopy, remove as iconRemove, move as iconMove } from '@/Libs/icon-class';

    export default {
        components: {
            BizButtonIcon,
        },

        props: {
            canDelete: {type: Boolean, default: true},
            canDuplicate: {type: Boolean, default: true},
            canMove: {type: Boolean, default: true},
        },

        emits: [
            'delete-content',
            'duplicate-content',
        ],

        setup() {
            return {
                iconCopy,
                iconRemove,
                iconMove,
            };
        },
    }
</script>
