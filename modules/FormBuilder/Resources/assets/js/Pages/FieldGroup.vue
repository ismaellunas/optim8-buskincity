<template>
    <div class="card">
        <header class="card-header pt-4 px-3">
            <biz-input
                v-model="computedFieldGroup.title"
                placeholder="Field Group Title"
                class="mb-4"
                style="width: 100%"
            />
        </header>

        <div class="card-content">
            <draggable
                class="dragArea list-group columns is-multiline builder-area"
                style="min-height: 200px"
                group="fields"
                handle=".handle-content"
                item-key="id"
                :animation="300"
                :empty-insert-threshold="emptyInsertThreshold"
                :list="computedFields"
                @change="log"
            >
                <template #item="{ element, index }">
                    <component
                        :is="element.type"
                        :id="element.id"
                        v-model="computedFields[index]"
                        class="component-configurable builder-area column"
                        :class="element.column"
                        :data-id="element.id"
                        @click="onSettingInput"
                        @delete-content="deleteContent"
                        @duplicate-content="onDuplicateInput"
                    />
                </template>
            </draggable>
        </div>
    </div>
</template>

<script>
    import BizInput from '@/Biz/Input.vue';
    import BizCard from '@/Biz/Card.vue';
    import BizButton from '@/Biz/Button.vue';
    import Draggable from "vuedraggable";
    import { cloneDeep } from 'lodash';
    import { confirmDelete } from '@/Libs/alert';
    import {
        isBlank,
        useModelWrapper,
        generateElementId
    } from '@/Libs/utils';

    // Form Builder Fields
    import Country from './../Fields/Inputs/Country.vue';
    import Email from './../Fields/Inputs/Email.vue';
    import FileDragDrop from './../Fields/Inputs/FileDragDrop.vue';
    import Number from './../Fields/Inputs/Number.vue';
    import Phone from './../Fields/Inputs/Phone.vue';
    import Postcode from './../Fields/Inputs/Postcode.vue';
    import Select from './../Fields/Inputs/Select.vue';
    import Text from './../Fields/Inputs/Text.vue';
    import Textarea from './../Fields/Inputs/Textarea.vue';

    export default {
        name: 'FieldGroup',

        components: {
            BizInput,
            BizCard,
            BizButton,
            Draggable,

            // Form Builder Fields
            Country,
            Email,
            FileDragDrop,
            Number,
            Phone,
            Postcode,
            Select,
            Text,
            Textarea,
        },

        props: {
            fieldGroup: { type: Object, required: true },
            isDebugMode: { type: Boolean, default: false },
            mappedFieldIds: { type: Array, default: () => [] },
        },

        emits: [
            'on-setting-input',
        ],

        setup(props, {emit}) {
            return {
                computedFieldGroup: useModelWrapper(props, emit, "fieldGroup"),
            };
        },

        computed: {
            computedFields() {
                return this.computedFieldGroup.fields;
            },

            isEmptyComponents() {
                return !Array.isArray(this.computedFields) || !this.computedFields.length;
            },

            isDraggableEmpty() {
                return this.isEmptyComponents;
            },

            emptyInsertThreshold() {
                return this.isDraggableEmpty ? 50 : 5;
            },
        },

        methods: {
            log: function(evt) {
                if (this.isDebugMode) {
                    console.log(evt);
                }
            },

            deleteContent(id) {
                let message = null;

                if (this.mappedFieldIds.includes(id)) {
                    message = 'If you remove this field, it will impact the settings of the "Automate user creation" feature.';
                }

                confirmDelete('Are you sure?', message).then((result) => {
                    if (result.isConfirmed) {
                        this.onDeleteInput(id);
                    }
                })
            },

            onDeleteInput(id) {
                if (!isBlank(id)) {
                    this.computedFields.splice(
                        this.computedFields.map(field => field.id).indexOf(id),
                        1
                    );
                }
            },

            onDuplicateInput(id) {
                if (!isBlank(id)) {
                    const duplicateField = cloneDeep(
                        this.computedFields[
                            this.computedFields.map(field => field.id).indexOf(id)
                        ]
                    );

                    duplicateField.id = generateElementId();

                    this.computedFields.push(duplicateField);
                }
            },

            onSettingInput(event) {
                let configComponent = event.target.closest('.component-configurable');

                if (configComponent) {
                    if (configComponent.hasAttribute('data-id')) {
                        this.$emit('on-setting-input', configComponent.getAttribute('data-id'));
                    }
                }
            },
        },
    }
</script>
