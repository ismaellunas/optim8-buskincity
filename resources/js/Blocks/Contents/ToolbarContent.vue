<template>
    <div class="edit-mode-toolbar-content">
        <div class="field has-addons is-pulled-right">
            <p
                v-if="canDuplicate"
                class="control"
            >
                <biz-button
                    type="button"
                    class="is-small"
                    @click="$emit('duplicate-content', $event)"
                >
                    <span class="icon">
                        <i class="fas fa-copy" />
                    </span>
                </biz-button>
            </p>
            <p
                v-if="canDelete"
                class="control"
            >
                <biz-button
                    type="button"
                    class="is-small"
                    @click="$emit('delete-content', $event)"
                >
                    <span class="icon">
                        <i class="fas fa-trash" />
                    </span>
                </biz-button>
            </p>
            <p
                v-if="canMove"
                class="control"
            >
                <biz-button
                    type="button"
                    class="is-small handle-content"
                >
                    <span class="icon">
                        <i class="fas fa-arrows-alt" />
                    </span>
                </biz-button>
            </p>
        </div>
    </div>
</template>

<script>
    import BizButton from '@/Biz/Button';

    export default {
        components: {
            BizButton,
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
    }
</script>
