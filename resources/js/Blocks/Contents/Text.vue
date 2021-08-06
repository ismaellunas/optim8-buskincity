<template>
    <div>
        <sdb-toolbar-content
            v-if="isEditMode"
            @delete-content="deleteContent"
        />
        <div
            v-if="isEditMode"
            class="content"
            :class="contentClass"
        >
            <sdb-tinymce
                v-model="entity.content.html"
                :class="ckeditorClass"
            />
        </div>
        <div
            v-else
            class="content"
            :class="contentClass"
            v-html="entity.content.html"
        >
        </div>
    </div>
</template>

<script>
    //import SdbCkeditorInline from '@/Sdb/CkeditorInline';
    //import SdbEditorTiptap from '@/Sdb/EditorTiptap';
    import DeletableContentMixin from '@/Mixins/DeletableContent';
    import EditModeContentMixin from '@/Mixins/EditModeContent';
    import SdbTinymce from '@/Sdb/EditorTinymce';
    import SdbToolbarContent from '@/Blocks/Contents/ToolbarContent';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        mixins: [
            DeletableContentMixin,
            EditModeContentMixin,
        ],
        components: {
            //SdbCkeditorInline,
            //SdbEditorTiptap,
            SdbTinymce,
            SdbToolbarContent,
        },
        props: {
            id: {},
            modelValue: {},
        },
        setup(props, { emit }) {
            return {
                config: props.modelValue.config,
                entity: useModelWrapper(props, emit),
            };
        },
        computed: {
            contentClass() {
                let classes = [];
                classes.push(this.config.text?.alignment ?? '');
                classes.push(this.config.text?.size ?? '');
                return classes.filter(Boolean);
            },
            ckeditorClass() {
                let classes = [];
                classes.push(this.config.text?.alignment ?? '');
                return classes.filter(Boolean);
            },
        }
    }
</script>
