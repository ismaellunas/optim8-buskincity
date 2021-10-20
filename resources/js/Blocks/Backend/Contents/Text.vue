<template>
    <div :class="wrapperClass">
        <sdb-toolbar-content
            @delete-content="deleteContent"
        />
        <div
            class="content"
            :class="contentClass"
        >
            <sdb-tinymce
                v-model="entity.content.html"
                :class="editorClass"
            />
        </div>
    </div>
</template>

<script>
    import DeletableContentMixin from '@/Mixins/DeletableContent';
    import SdbTinymce from '@/Sdb/EditorTinymce';
    import SdbToolbarContent from '@/Blocks/Backend/Contents/ToolbarContent';
    import { concat } from 'lodash';
    import { createMarginClasses, createPaddingClasses } from '@/Libs/page-builder';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'Text',
        mixins: [
            DeletableContentMixin,
        ],
        components: {
            SdbTinymce,
            SdbToolbarContent,
        },
        props: {
            id: String,
            modelValue: Object,
        },
        setup(props, { emit }) {
            return {
                config: props.modelValue.config,
                entity: useModelWrapper(props, emit),
            };
        },
        computed: {
            contentClass() {
                return concat(
                    (this.config.text?.alignment ?? ''),
                    (this.config.text?.size ?? '')
                ).filter(Boolean);
            },
            editorClass() {
                return concat(
                    (this.config.text?.alignment ?? '')
                ).filter(Boolean);
            },
            wrapperClass() {
                return concat(
                    createPaddingClasses(this.config.wrapper?.padding),
                    createMarginClasses(this.config.wrapper?.margin)
                ).filter(Boolean);
            }
        }
    }
</script>
