<template>
    <div :style="dimensionStyle">
        <biz-toolbar-content
            @delete-content="deleteContent"
            @duplicate-content="duplicateContent"
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
    import MixinContentHasDimension from '@/Mixins/ContentHasDimension';
    import MixinDeletableContent from '@/Mixins/DeletableContent';
    import MixinDuplicableContent from '@/Mixins/DuplicableContent';
    import BizTinymce from '@/Biz/EditorTinymce.vue';
    import BizToolbarContent from '@/Blocks/Contents/ToolbarContent.vue';
    import FaqQuestionAnswer from '@/Blocks/Contents/Faq/QuestionAnswer.vue';
    import { last } from 'lodash';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'ContentFaq',
        components: {
            BizTinymce,
            BizToolbarContent,
            FaqQuestionAnswer,
        },
        mixins: [
            MixinContentHasDimension,
            MixinDeletableContent,
            MixinDuplicableContent
        ],
        props: {
            id: {type: String, default: null},
            modelValue: {type: Object, required: true},
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
    };
</script>