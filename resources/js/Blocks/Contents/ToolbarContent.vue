<template>
    <div class="edit-mode-toolbar-content">
        <div class="field has-addons is-pulled-right">
            <p class="control" v-if="canDelete">
                <biz-button type="button" class="is-small" @click="$emit('delete-content', $event)">
                    <span class="icon"><i class="fas fa-trash"></i></span>
                </biz-button>
            </p>
            <p class="control" v-if="canMove">
                <biz-button type="button" class="is-small handle-content">
                    <span class="icon"><i class="fas fa-arrows-alt"></i></span>
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
        emits: [
            'delete-content',
        ],
        props: {
            canDelete: {type: Boolean, default: true},
            canMove: {type: Boolean, default: true},
        },
    }
</script>
