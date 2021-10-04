<template>
    <main>
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
                        v-model="entity.content.heroContent.body.title.html"
                        :class="editorClass"
                    />
                </p>
                <p
                    v-else
                    class="title"
                    v-html="entity.content.heroContent.body.title.html"
                >
                </p>
            </div>
        </div>
        <div class="columns px-4">
            <div class="column">
                <nested-question
                    :items="entity.content.faqContent.contents"
                    :is-edit-mode="isEditMode"
                ></nested-question>
            </div>
        </div>
    </main>
</template>

<script>
    import DeletableContentMixin from '@/Mixins/DeletableContent';
    import EditModeComponentMixin from '@/Mixins/EditModeComponent';
    import SdbTinymce from '@/Sdb/EditorTinymce';
    import SdbToolbarContent from '@/Blocks/Contents/ToolbarContent';
    import NestedQuestion from '@/Blocks/Contents/Faq/NestedQuestion';
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
            NestedQuestion,
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
                    (this.config.hero?.color ?? ''),
                ).filter(Boolean);
            }
        }
    }
</script>