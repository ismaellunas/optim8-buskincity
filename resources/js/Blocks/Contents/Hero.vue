<template>
    <div class="hero" :class="heroClass">
        <sdb-toolbar-content
            v-if="isEditMode"
            @delete-content="deleteContent"
        />
        <div class="hero-body">
            <p
                v-if="isEditMode"
                class="title"
            >
                <sdb-tinymce
                    v-model="entity.content.body.title.html"
                    :class="editorClass"
                />
            </p>
            <p
                v-else
                class="title"
                v-html="entity.content.body.title.html"
            >
            </p>

            <p
                v-if="isEditMode"
                class="subtitle"
            >
                <sdb-tinymce
                    v-model="entity.content.body.subtitle.html"
                    :class="editorClass"
                />
            </p>
            <p
                v-else
                class="subtitle"
                v-html="entity.content.body.subtitle.html"
            >
            </p>
        </div>
    </div>
</template>

<script>
    import DeletableContentMixin from '@/Mixins/DeletableContent';
    import EditModeComponentMixin from '@/Mixins/EditModeComponent';
    import SdbTinymce from '@/Sdb/EditorTinymce';
    import SdbToolbarContent from '@/Blocks/Contents/ToolbarContent';
    import { concat } from 'lodash';
    import { createMarginClasses, createPaddingClasses } from '@/Libs/page-builder';
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
            editorClass() {
                return concat(
                    (this.config.hero?.alignment ?? '')
                ).filter(Boolean);
            },
            heroClass() {
                return concat(
                    (this.config.hero?.alignment ?? ''),
                    (this.config.hero?.size ?? ''),
                    (this.config.hero?.color ?? ''),
                ).filter(Boolean);
            }
        }
    }
</script>
