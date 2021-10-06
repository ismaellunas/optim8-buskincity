<template>
    <main>
        <div class="hero" :class="heroClass">
            <sdb-toolbar-content
                v-if="isEditMode"
                @delete-content="deleteContent"
            />
            <div class="hero-body">
                <template v-if="isEditMode">
                    <component
                        is="p"
                        class="title"
                        :class="editorClass"
                        contenteditable
                        @blur="onEditTitle"
                        v-text="entity.content.heroContent.body.title.html"
                    >
                    </component>
                </template>

                <template v-else>
                    <component
                        is="p"
                        class="title"
                        v-text="entity.content.heroContent.body.title.html"
                    >
                    </component>
                </template>
            </div>
        </div>
        <nested-question
            :items="entity.content.faqContent.contents"
            :is-edit-mode="isEditMode"
            :template="entity.content.faqContent.template"
            is-child-open="false"
        ></nested-question>
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
        },
        methods: {
            onEditTitle(evt){
                this.entity.content.heroContent.body.title.html = evt.target.innerText;
            },
        }
    }
</script>