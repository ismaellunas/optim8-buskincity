<template>
    <div class="card sdb-card-text" :class="{'edit-mode-content': isEditMode}">
        <div class="card-content">
            <div class="content">
                <template v-if="isEditMode">
                    <sdb-ckeditor-inline v-model="content.cardContent.content.html"/>
                </template>
                <template v-else>
                    <div v-html="content.cardContent.content.html"></div>
                </template>
            </div>
        </div>
        <div class="edit-mode-buttons" v-if="isEditMode">
            <button class="button is-small" type="button" @click="deleteContent">
                <i class="fa fa-trash" aria-hidden="true"></i>
            </button>
        </div>
    </div>
</template>

<script>
    import SdbCkeditorInline from '@/Sdb/CkeditorInline'
    import { useModelWrapper, emitModelValue } from '@/Libs/utils'

    export default {
        components: {
            SdbCkeditorInline,
        },
        props: {
            id: {},
            isEditMode: {type: Boolean, default: false},
            modelValue: {},
        },
        setup(props, { emit }) {
            return {
                content: useModelWrapper(props, emit),
            };
        },
        methods: {
            deleteContent() {
                this.$emit('delete-content', this.id);
            },
        }
    }
</script>

<style scoped src="../../../css/column-content.css"></style>
