<template>
    <div class="edit-mode-toolbar-content">
        <div class="field has-addons is-pulled-right">
            <p class="control" v-if="canDelete">
                <sdb-button type="button" class="is-small" @click="deleteContent">
                    <span class="icon"><i class="fas fa-trash"></i></span>
                </sdb-button>
            </p>
            <p class="control" v-if="canMove">
                <sdb-button type="button" class="is-small handle-content">
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
        props: {
            canDelete: {type: Boolean, default: true},
            canMove: {type: Boolean, default: true},
        },
        methods: {
            deleteContent() {
                this.$emit('delete-content');
            },
        }
    }
</script>
