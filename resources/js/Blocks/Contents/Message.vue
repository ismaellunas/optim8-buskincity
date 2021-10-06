<template>
    <article class="message" :class="messageClass">
        <sdb-toolbar-content
            v-if="isEditMode"
            @delete-content="deleteContent"
        />
        <div
            v-if="isEditMode"
            class="message-body content"
        >
            <sdb-tinymce
                v-model="entity.content.html"
            />
        </div>
        <div
            v-else
            class="message-body content"
            v-html="entity.content.html"
        >
        </div>
    </article>
</template>

<script>
    import DeletableContentMixin from '@/Mixins/DeletableContent';
    import EditModeComponentMixin from '@/Mixins/EditModeComponent';
    import SdbTinymce from '@/Sdb/EditorTinymce';
    import SdbToolbarContent from '@/Blocks/Contents/ToolbarContent';
    import { concat } from 'lodash';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        mixins: [
            DeletableContentMixin,
            EditModeComponentMixin,
        ],
        components: {
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
            messageClass() {
                return concat(
                    (this.config.message?.size ?? ''),
                    (this.config.message?.color ?? ''),
                ).filter(Boolean);
            }
        }
    }
</script>
