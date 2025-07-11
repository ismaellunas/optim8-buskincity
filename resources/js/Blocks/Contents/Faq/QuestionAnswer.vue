<template>
    <div class="column p-0">
        <draggable
            tag="div"
            :list="computedItems"
            class="mb-3"
            handle=".handle-content"
            item-key="id"
            :animation="300"
        >
            <template #item="{ element, index }">
                <article class="media">
                    <div class="media-left">
                        <i class="fas fa-question-circle" />
                    </div>
                    <div class="media-content">
                        <biz-toolbar-content
                            :can-duplicate="false"
                            @delete-content="deleteConfirm(element.id)"
                        />
                        <div
                            class="content has-text-weight-bold"
                        >
                            <p
                                class="border-dash"
                                contenteditable
                                @blur="onEditQuestion($event, index)"
                                v-text="element.question"
                            />
                        </div>

                        <article class="media">
                            <div
                                class="content border-dash"
                                style="width: 100%"
                            >
                                <biz-form-text-editor-full-inline
                                    v-model="element.answer"
                                />
                            </div>
                        </article>
                    </div>
                </article>
            </template>
        </draggable>

        <biz-button
            type="button"
            class="is-small"
            @click="addQuestion()"
        >
            Add Question
        </biz-button>
    </div>
</template>

<script>
    import draggable from 'vuedraggable';
    import BizButton from '@/Biz/Button.vue';
    import BizFormTextEditorFullInline from '@/Biz/Form/TextEditorFullInline.vue';
    import BizToolbarContent from '@/Blocks/Contents/ToolbarContent.vue';
    import { generateElementId, useModelWrapper } from '@/Libs/utils';
    import { cloneDeep } from 'lodash';
    import { confirmDelete } from '@/Libs/alert';

    export default {
        name: 'FaqQuestionAnswer',

        components: {
            draggable,
            BizButton,
            BizFormTextEditorFullInline,
            BizToolbarContent,
        },

        props: {
            items: {
                required: true,
                type: Array
            },
            template: {
                type: Object,
                required: true,
            },
        },

        setup(props, { emit }) {
            return {
                computedItems: useModelWrapper(props, emit, 'items'),
            };
        },

        methods: {
            onEditQuestion(evt, index){
                this.computedItems[index].question = evt.target.innerText;
            },

            addQuestion() {
                const question = this.template.question;
                question.id = generateElementId();
                this.computedItems.push(cloneDeep(question));
            },

            deleteConfirm(id) {
                const self = this;
                confirmDelete('Are you sure?').then((result) => {
                    if (result.isConfirmed) {
                        self.deleteQuestion(id);
                    }
                });
            },

            deleteQuestion(id) {
                const index = this.computedItems.findIndex(item => item.id == id);

                if (index > -1) {
                    this.computedItems.splice(index, 1);
                }
            },
        },
    }
</script>