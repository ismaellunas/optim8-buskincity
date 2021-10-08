<template>
    <div class="column p-0">
        <draggable
            tag="div"
            :list="items"
            class="mb-3"
            handle=".handle-content"
            item-key="id"
            :animation="300"
            :disabled="!isEditMode"
        >
            <template #item="{ element, index }">
                <article class="media">
                    <div class="media-left">
                        <i class="fas fa-question-circle"></i>
                    </div>
                    <div class="media-content">
                        <sdb-toolbar-content
                            v-if="isEditMode"
                            @delete-content="deleteConfirm(element.id)"
                        />
                        <div
                            v-if="isEditMode"
                            class="content has-text-weight-bold"
                        >
                            <p
                                class="border-dash"
                                contenteditable
                                @blur="onEditQuestion($event, index)"
                                v-text="element.question"
                            >
                            </p>
                        </div>
                        <div
                            v-else
                            class="content has-text-weight-bold"
                        >
                            <p v-html="element.question"></p>
                        </div>

                        <article class="media">
                            <div
                                v-if="isEditMode"
                                class="content border-dash"
                                style="width: 100%"
                            >
                                <sdb-form-text-editor-full-inline
                                    v-model="element.answer"
                                />
                            </div>
                            <div
                                v-else
                                class="content"
                                v-html="element.answer"
                            >
                            </div>
                        </article>
                    </div>
                </article>
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
    import SdbFormTextEditorFullInline from '@/Sdb/Form/TextEditorFullInline';
    import SdbToolbarContent from '@/Blocks/Contents/ToolbarContent';
    import { generateElementId } from '@/Libs/utils';
    import { cloneDeep } from 'lodash';
    import { confirmDelete } from '@/Libs/alert';

    export default {
        components: {
            draggable,
            SdbButton,
            SdbFormTextEditorFullInline,
            SdbToolbarContent,
        },

        props: {
            items: {
                required: true,
                type: Array
            },
            isEditMode: Boolean,
            template: Object,
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
                    this.items.splice(index, 1);
                }
            },
        },
    }
</script>

<style scoped>
    .border-dash {
        border: 1px #D3D3D3 dashed;
    }
</style>