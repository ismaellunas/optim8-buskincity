<template>
    <div>
        <component
            :is="headingTag"
            :class="headingClass"
            v-text="entity.content.heading.html"
        />
        <faq-question-answer
            :items="entity.content.faqContent.contents"
        />
    </div>
</template>

<script>
    import FaqQuestionAnswer from '@/Blocks/Frontend/Contents/Faq/QuestionAnswer';
    import { last } from 'lodash';

    export default {
        name: 'Faq',
        components: {
            FaqQuestionAnswer,
        },
        props: {
            id: {type: String, default: null},
            entity: {type: Object, default:() => {}},
        },
        setup(props) {
            return {
                config: props.entity?.config,
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
        }
    }
</script>