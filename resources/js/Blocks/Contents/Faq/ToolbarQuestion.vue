<template>
    <div class="edit-mode-toolbar-content edit-mode-toolbar-question">
        <div class="field has-addons is-pulled-right">
            <p class="control" v-if="canDelete">
                <sdb-button type="button" class="is-small" @click="$emit('delete-question', $event)">
                    <span class="icon"><i class="fas fa-trash"></i></span>
                </sdb-button>
            </p>
            <p class="control" v-if="canMove">
                <sdb-button type="button" class="is-small handle-question">
                    <span class="icon"><i class="fas fa-arrows-alt"></i></span>
                </sdb-button>
            </p>
        </div>
    </div>
</template>

<script>
    import SdbButton from '@/Sdb/Button';

    export default {
        components: {
            SdbButton,
        },
        emits: [
            'delete-question',
        ],
        props: {
            canDelete: {type: Boolean, default: true},
            canMove: {type: Boolean, default: true},
        },
    }
</script>

<style scoped>
    .edit-mode-toolbar-question {
        margin-right: 20px;
    }
</style>
