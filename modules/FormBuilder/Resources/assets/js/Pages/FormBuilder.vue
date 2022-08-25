<template>
    <div>
        <div class="columns">
            <div class="column">
                <biz-form-input
                    v-model="form.name"
                    label="Name"
                    placeholder="Contact Form"
                    :required="true"
                    :message="error('name')"
                />
            </div>
            <div class="column">
                <biz-form-input
                    v-model="form.key"
                    label="Key"
                    placeholder="e.g. contact_form"
                    :required="true"
                    :message="error('key')"
                />
            </div>
        </div>

        <hr>

        <div class="columns">
            <div class="column is-3 is-narrow">
                <div id="side-menu-form-builder">
                    <template v-if="!isComponentConfigOpen">
                        <draggable
                            class="dragArea columns is-multiline"
                            :list="availableFields"
                            :group="{ name: 'fields', pull: 'clone', put: false }"
                            :clone="cloneComponent"
                            :sort="false"
                            item-key="id"
                            @change="log"
                        >
                            <template #item="{ element }">
                                <div class="column is-half">
                                    <div
                                        class="card"
                                    >
                                        <div class="card-content is-size-7">
                                            <div class="content is-center">
                                                {{ element.title }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </draggable>
                    </template>

                    <template v-else>
                        <input-config
                            v-model="fieldByConfigId"
                            class="form-builder-content-config"
                        />
                    </template>
                </div>
            </div>

            <div
                class="column is-9"
                :class="builderClasses"
            >
                <draggable
                    class="dragArea list-group columns is-multiline"
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
                            @click="settingContent"
                            @delete-content="deleteContent"
                            @duplicate-content="duplicateContent"
                        />
                    </template>
                </draggable>
            </div>
        </div>

        <div class="field is-grouped is-grouped-right">
            <div class="control">
                <biz-button-link
                    class="is-link is-light"
                    :href="route(baseRouteName + '.index')"
                >
                    Cancel
                </biz-button-link>
            </div>
            <div class="control">
                <biz-button
                    class="is-link"
                    @click="onSubmit"
                >
                    {{ !isEditMode ? 'Create' : 'Update' }}
                </biz-button>
            </div>
        </div>
    </div>
</template>

<script>
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import MixinHasLoader from '@/Mixins/HasLoader';
    import BizButton from '@/Biz/Button';
    import BizButtonLink from '@/Biz/ButtonLink';
    import BizFormInput from '@/Biz/Form/Input';
    import FieldStructures from './../FieldStructures';
    import Draggable from "vuedraggable";
    import Email from './../Blocks/Inputs/Email';
    import InputConfig from './../Blocks/InputConfig';
    import Number from './../Blocks/Inputs/Number';
    import Select from './../Blocks/Inputs/Select';
    import Text from './../Blocks/Inputs/Text';
    import Textarea from './../Blocks/Inputs/Textarea';
    import { cloneDeep } from 'lodash';
    import { createColumn } from './../Libs/form-builder.js';
    import { usePage } from '@inertiajs/inertia-vue3';
    import {
        isBlank,
        useModelWrapper,
        generateElementId,
    } from '@/Libs/utils';

    export default {
        name: 'FormBuilder',

        components: {
            BizButton,
            BizButtonLink,
            BizFormInput,
            Draggable,
            Email,
            InputConfig,
            Number,
            Select,
            Text,
            Textarea,
        },

        mixins: [
            MixinHasPageErrors,
            MixinHasLoader,
        ],

        inject: {
            isEditMode: { default: false },
        },

        props: {
            contentConfigId: { type: String, default: "" },
            modelValue: { type: Object, required: true },
        },

        emits: [
            'update:contentConfigId',
        ],

        setup(props, {emit}) {
            return {
                form: useModelWrapper(props, emit),
                computedContentConfigId: useModelWrapper(props, emit, 'contentConfigId'),
                isDebugMode: false,
                baseRouteName: usePage().props.value.baseRouteName,
            };
        },

        data() {
            return {
                clonedComponent: null,
            };
        },

        computed: {
            computedFields() {
                return this.form.builders.fields;
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

            builderClasses() {
                return {
                    'has-background-grey-lighter has-text-centered': !this.hasFields,
                    'builder-area': this.hasFields
                };
            },

            fieldByConfigId() {
                const index = this.computedFields.findIndex(field => field.id == this.contentConfigId);

                return this.computedFields[index] ?? {};
            },

            isComponentConfigOpen() {
                return (
                    !isBlank(this.fieldByConfigId)
                    && !isBlank(this.contentConfigId)
                );
            },

            availableFields() {
                let components = [];
                for (const property in FieldStructures) {
                    components.push(FieldStructures[property]);
                }
                return components;
            },

            hasFields() {
                return isBlank(this.form.builders.fields) ? false : this.form.builders.fields.length > 0;
            },
        },

        methods: {
            log: function(evt) {
                if (this.isDebugMode) {
                    console.log(evt);
                }
            },

            cloneComponent(seletectedComponent) {
                this.clonedComponent = JSON.parse(JSON.stringify(seletectedComponent));
                this.clonedComponent.id = generateElementId();

                return this.clonedComponent;
            },

            settingContent(event) {
                let configComponent = event.target.closest('.component-configurable');

                if (configComponent) {
                    if (configComponent.hasAttribute('data-id')) {
                        this.computedContentConfigId = configComponent.getAttribute('data-id');
                    }
                }
            },

            onSubmit() {
                const self = this;

                self.form.post(
                    route(self.baseRouteName + '.store'),
                    {
                        onStart: () => self.onStartLoadingOverlay(),
                        onFinish: () => self.onEndLoadingOverlay(),
                    }
                )
            },

            deleteContent(id) {
                if (!isBlank(id)) {
                    this.computedFields.splice(
                        this.computedFields.map(field => field.id).indexOf(id),
                        1
                    );
                }
            },

            duplicateContent(id) {
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
        },
    }
</script>