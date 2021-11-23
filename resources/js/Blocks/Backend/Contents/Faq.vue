<template>
    <div>
        <sdb-toolbar-content
            @delete-content="deleteContent"
        />
        <component
            :is="headingTag"
            :class="headingClass"
            contenteditable
            @blur="onEditHeading"
            v-text="entity.content.heading.html"
        />
        <faq-question-answer
            :items="entity.content.faqContent.contents"
            :template="entity.content.faqContent.template"
        />
    </div>
</template>

<script>
    import DeletableContentMixin from '@/Mixins/DeletableContent';
    import EditModeComponentMixin from '@/Mixins/EditModeComponent';
    import SdbTinymce from '@/Sdb/EditorTinymce';
    import SdbToolbarContent from '@/Blocks/Backend/Contents/ToolbarContent';
    import FaqQuestionAnswer from '@/Blocks/Backend/Contents/Faq/QuestionAnswer';
    import { last } from 'lodash';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'Faq',
        components: {
            SdbTinymce,
            SdbToolbarContent,
            FaqQuestionAnswer,
        },
        mixins: [
            DeletableContentMixin,
            EditModeComponentMixin,
        ],
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