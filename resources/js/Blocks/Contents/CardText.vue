<template>
    <div>
        <sdb-toolbar-content @delete-content="deleteContent"/>

        <div class="card sdb-card-text">
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
        </div>
    </div>
</template>

<script>
    import SdbCkeditorInline from '@/Sdb/CkeditorInline'
    import SdbToolbarContent from '@/Blocks/Contents/ToolbarContent';
    import { useModelWrapper } from '@/Libs/utils'

    export default {
        components: {
            SdbCkeditorInline,
            SdbToolbarContent,
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
