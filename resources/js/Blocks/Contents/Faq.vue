<template>
    <div>
        <sdb-toolbar-content
            v-if="isEditMode"
            @delete-content="deleteContent"
        />
        <template v-if="isEditMode">
            <component
                :is="headingTag"
                :class="headingClass"
                contenteditable
                @blur="onEditHeading"
                v-text="entity.content.heading.html"
            >
            </component>
        </template>

        <template v-else>
            <component
                :is="headingTag"
                :class="headingClass"
                v-text="entity.content.heading.html"
            >
            </component>
        </template>
        <faq-question-answer
            :items="entity.content.faqContent.contents"
            :is-edit-mode="isEditMode"
            :template="entity.content.faqContent.template"
        ></faq-question-answer>

    </div>
</template>

<script>
    import DeletableContentMixin from '@/Mixins/DeletableContent';
    import EditModeComponentMixin from '@/Mixins/EditModeComponent';
    import SdbTinymce from '@/Sdb/EditorTinymce';
    import SdbToolbarContent from '@/Blocks/Contents/ToolbarContent';
    import FaqQuestionAnswer from '@/Blocks/Contents/Faq/QuestionAnswer';
    import { last } from 'lodash';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'Faq',
        mixins: [
            DeletableContentMixin,
            EditModeComponentMixin,
        ],
        components: {
            SdbTinymce,
            SdbToolbarContent,
            FaqQuestionAnswer,
        },
        props: {
            id: {type: String},
            modelValue: {type: Object},
        },
        setup(props, { emit }) {
            return {
                config: props.modelValue.config,
                entity: useModelWrapper(props, emit),
            };
        },
        computed: {
            headingTag() {
                return this.config.heading?.tag ?? 'h1';
            },
            headingClass() {
                let classes = [];
                classes.push(this.config.heading?.type ?? 'title');
                classes.push('is-' + last(this.headingTag));
                classes.push(this.config.heading?.alignment ?? "");
                return classes;
            },
        },
        methods: {
            onEditHeading(evt){
                this.entity.content.heading.html = evt.target.innerText;
            },
        }
    }
</script>