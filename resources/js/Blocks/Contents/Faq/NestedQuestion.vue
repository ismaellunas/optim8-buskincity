<template>
    <div class="column p-0 question-nested" :class="isChildOpen ? 'open' : ''">
        <draggable
            :class="isEditMode ? 'dragArea' : ''"
            tag="ul"
            :list="items"
            group="questions"
            handle=".handle-question"
            :animation="300"
            :disabled="!isEditMode"
        >
            <template #item="{ element, index }">
                <li :class="isEditMode ? 'question' : 'question-frontend'">
                    <sdb-toolbar-question
                        v-if="isEditMode"
                        @delete-question="deleteConfirm(element.id)"
                    />
                    <div class="question-body">
                        <div
                            v-if="isEditMode"
                            class="has-text-weight-bold"
                        >
                            Q:
                            <component
                                is="div"
                                contenteditable
                                @blur="onEditQuestion($event, index)"
                                v-text="element.question"
                            >
                            </component>
                        </div>
                        <div
                            v-else
                            class="question-title has-text-weight-bold"
                            @click="toggleOpen(index)"
                        >
                            <i class="fas fa-question-circle"></i>
                            {{ element.question }}
                        </div>

                        <template v-if="element.childs.length == 0">
                            <div v-if="isEditMode" class="content">
                                A:
                                <sdb-tinymce
                                    v-model="element.answer"
                                />
                            </div>
                            <div
                                v-else
                                class="content question-answer"
                                :class="element.isAnswerOpen ? 'open' : ''"
                                v-html="element.answer"
                            >
                            </div>
                        </template>
                    </div>
                    <nested-question
                        v-if="element.answer == null || element.answer == ''"
                        :is-edit-mode="isEditMode"
                        :items="element.childs"
                        :template="template"
                        :is-child-open="element.isChildOpen"
                    />
                </li>
            </template>
        </draggable>

        <sdb-button
            v-if="isEditMode"
            type="button"
            class="is-small"
            @click="addQuestion()"
        >
            Add Question
        </sdb-button>
    </div>
</template>

<script>
    import draggable from "vuedraggable";
    import SdbButton from '@/Sdb/Button';
    import SdbTinymce from '@/Sdb/EditorTinymce';
    import SdbToolbarQuestion from '@/Blocks/Contents/Faq/ToolbarQuestion';
    import { generateElementId } from '@/Libs/utils';
    import { cloneDeep } from 'lodash';
    import { confirmDelete } from '@/Libs/alert';

    export default {
        name: "nested-question",

        components: {
            draggable,
            SdbButton,
            SdbTinymce,
            SdbToolbarQuestion,
        },

        props: {
            items: {
                required: true,
                type: Array
            },
            isEditMode: Boolean,
            template: Object,
            isChildOpen: Boolean,
        },

        methods: {
            onEditQuestion(evt, index){
                this.items[index].question = evt.target.innerText;
            },

            addQuestion() {
                const question = this.template.question;
                question.id = generateElementId();
                this.items.push(cloneDeep(question));
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
                const index = this.items.findIndex(item => item.id == id);

                if (index > -1) {
                    this.moveChild(this.items[index].childs);
                    this.items.splice(index, 1);
                }
            },

            moveChild(childs) {
                if (childs.length > 0) {
                    this.items.push(...childs);
                }
            },

            toggleOpen(index) {
                this.items[index].isAnswerOpen = !this.items[index].isAnswerOpen;
                if (this.items[index].childs.length > 0) {
                    this.items[index].isChildOpen = !this.items[index].isChildOpen;
                }
            },
        },
    }
</script>

<style scoped>
    .question {
        width: 100%;
        height: auto;
        padding: 10px 0 10px 0;
    }

    .question-frontend {
        width: 100%;
        height: auto;
    }

    .dragArea {
        border: 1px #D3D3D3 dashed;
        padding: 5px 0px 5px 20px;
    }

    .question-frontend .question-body .question-title {
        position: relative;
        padding: 10px;
        background: #fff;
        cursor: pointer;
        border-bottom: 1px solid #ccc;
        border-left: 1px solid #ccc;
        border-right: 1px solid #ccc;
    }

    .question-frontend .question-body .question-title:hover {
        background: #ecf0f1;
    }

    .question-frontend .question-body .question-answer {
        position: relative;
        background: #ecf0f1;
        height: 0;
        overflow: hidden;
        overflow-y: auto;
        border-bottom: 1px solid #ccc;
        border-left: 1px solid #ccc;
        border-right: 1px solid #ccc;
    }

    .question-frontend .question-body .question-answer.open {
        height: auto;
        padding: 10px;
    }

    .question-frontend .question-nested {
        position: relative;
        height: 0;
        overflow: hidden;
        overflow-y: auto;
    }

    .question-frontend .question-nested.open {
        height: auto;
    }
</style>