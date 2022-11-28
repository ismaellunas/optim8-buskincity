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
                    @on-blur="populateKey"
                />
            </div>
            <div class="column">
                <biz-form-key
                    v-model="form.form_id"
                    label="Form ID"
                    placeholder="e.g. contact_form"
                    :required="true"
                    :message="error('form_id')"
                />
            </div>
        </div>

        <hr>

        <div class="columns">
            <div class="column is-3 is-narrow pt-0">
                <div
                    id="side-menu-form-builder"
                    class="p-1"
                >
                    <template v-if="!isComponentConfigOpen">
                        <biz-card
                            class="mb-1"
                            :is-collapsed="true"
                            :is-expanding-on-load="true"
                        >
                            <template #headerTitle>
                                General
                            </template>

                            <draggable
                                class="dragArea columns is-multiline"
                                :list="availableFields"
                                :group="{ name: 'fields', pull: 'clone', put: false }"
                                :clone="cloneComponent"
                                :sort="false"
                                item-key="id"
                                @change="log"
                                @start="onStartedHandler"
                                @end="onEndedHandler"
                            >
                                <template #item="{ element }">
                                    <div class="column is-half p-2">
                                        <div class="card">
                                            <div class="card-content is-size-7">
                                                <div class="content is-center">
                                                    {{ element.title }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </draggable>
                        </biz-card>
                    </template>

                    <template v-else>
                        <input-config
                            v-model="fieldByConfigId"
                            class="form-builder-input-config"
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
                            @click="settingInput"
                            @delete-content="deleteInput"
                            @duplicate-content="duplicateInput"
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
    import BizCard from '@/Biz/Card';
    import BizFormInput from '@/Biz/Form/Input';
    import BizFormKey from '@/Biz/Form/Key';
    import Draggable from "vuedraggable";
    import Email from './../Fields/Inputs/Email';
    import FieldStructures from './../FieldStructures';
    import InputConfig from './../Fields/InputConfig';
    import Number from './../Fields/Inputs/Number';
    import Phone from './../Fields/Inputs/Phone';
    import Select from './../Fields/Inputs/Select';
    import Text from './../Fields/Inputs/Text';
    import Textarea from './../Fields/Inputs/Textarea';
    import { cloneDeep, isEmpty } from 'lodash';
    import { usePage } from '@inertiajs/inertia-vue3';
    import {
        isBlank,
        useModelWrapper,
        generateElementId,
        convertToKey,
    } from '@/Libs/utils';

    export default {
        name: 'FormBuilder',

        components: {
            BizButton,
            BizButtonLink,
            BizCard,
            BizFormInput,
            BizFormKey,
            Draggable,
            Email,
            InputConfig,
            Number,
            Phone,
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
            inputConfigId: { type: String, default: "" },
            modelValue: { type: Object, required: true },
        },

        emits: [
            'update:inputConfigId',
        ],

        setup(props, {emit}) {
            return {
                form: useModelWrapper(props, emit),
                computedInputConfigId: useModelWrapper(props, emit, 'inputConfigId'),
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
                const index = this.computedFields.findIndex(field => field.id == this.inputConfigId);

                return this.computedFields[index] ?? {};
            },

            isComponentConfigOpen() {
                return (
                    !isBlank(this.fieldByConfigId)
                    && !isBlank(this.inputConfigId)
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

                this.computedInputConfigId = this.clonedComponent.id;

                return this.clonedComponent;
            },

            settingInput(event) {
                let configComponent = event.target.closest('.component-configurable');

                if (configComponent) {
                    if (configComponent.hasAttribute('data-id')) {
                        this.computedInputConfigId = configComponent.getAttribute('data-id');
                    }
                }
            },

            onSubmit() {
                const self = this;

                if (!this.isEditMode) {
                    self.form.post(
                        route(self.baseRouteName + '.store'),
                        {
                            onStart: () => self.onStartLoadingOverlay(),
                            onFinish: () => self.onEndLoadingOverlay(),
                        }
                    )
                } else {
                    self.form.put(
                        route(self.baseRouteName + '.update', self.form.id),
                        {
                            onStart: () => self.onStartLoadingOverlay(),
                            onFinish: () => self.onEndLoadingOverlay(),
                        }
                    )
                }
            },

            deleteInput(id) {
                if (!isBlank(id)) {
                    this.computedFields.splice(
                        this.computedFields.map(field => field.id).indexOf(id),
                        1
                    );
                }
            },

            duplicateInput(id) {
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

            populateKey(event) {
                if (isEmpty(this.form.form_id) && !isEmpty(this.form.name)) {
                    this.form.form_id = convertToKey(this.form.name);
                }
            },

            onStartedHandler(event) {
                event.item.classList.remove('is-half');
                event.item.classList.add('is-full');
            },

            onEndedHandler(event) {
                event.item.classList.remove('is-full');
                event.item.classList.add('is-half');
            }
        },
    }
</script>