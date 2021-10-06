<template>
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
                    v-text="entity.content.body.title.html"
                >
                </component>
            </template>
            <template v-else>
                <component
                    is="p"
                    class="title"
                    v-text="entity.content.body.title.html"
                />
            </template>

            <div
                v-if="isEditMode"
                class="subtitle content"
            >
                <sdb-tinymce
                    v-model="entity.content.body.subtitle.html"
                    :class="editorClass"
                />
            </div>
            <div
                v-else
                class="subtitle content"
                v-html="entity.content.body.subtitle.html"
            >
            </div>
        </div>
    </div>
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
        },
        methods: {
            onEditTitle(evt){
                this.entity.content.body.title.html = evt.target.innerText;
            },
        }
    }
</script>
