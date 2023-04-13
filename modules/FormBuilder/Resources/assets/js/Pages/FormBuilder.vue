<template>
    <div>
        <div class="columns">
            <div class="column">
                <biz-form-input
                    v-model="form.name"
                    :label="i18n.name"
                    placeholder="Contact Form"
                    :required="true"
                    :message="error('name')"
                    @on-blur="populateKey"
                />
            </div>
            <div class="column">
                <biz-form-key
                    v-model="form.form_id"
                    :label="i18n.form_id"
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
                                {{ i18n.general }}
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
            >
                <draggable
                    class="dragArea list-group columns is-multiline"
                    group="fieldGroups"
                    handle=".handle-field-group"
                    item-key="id"
                    :animation="300"
                    :list="computedFieldGroups"
                >
                    <template #item="{ element, index }">
                        <div class="column is-12">
                            <div class="field has-addons is-pulled-right">
                                <p class="control">
                                    <biz-button
                                        type="button"
                                        class="is-small"
                                        @click="deleteFieldGroup(index)"
                                    >
                                        <span class="icon">
                                            <i :class="iconRemove" />
                                        </span>
                                    </biz-button>
                                </p>
                                <p class="control">
                                    <biz-button
                                        type="button"
                                        class="is-small handle-field-group"
                                    >
                                        <span class="icon">
                                            <i :class="iconMove" />
                                        </span>
                                    </biz-button>
                                </p>
                            </div>

                            <div class="is-clearfix" />

                            <field-group
                                :field-group="element"
                                @on-setting-input="onSettingInput"
                            />
                        </div>
                    </template>
                </draggable>

                <div class="columns">
                    <div class="column is-12">
                        <biz-button
                            class="is-primary"
                            @click="addFieldGroup()"
                        >
                            {{ i18n.add_field_group }}
                        </biz-button>
                    </div>
                </div>
            </div>
        </div>

        <div class="field is-grouped is-grouped-right">
            <div class="control">
                <biz-button-link
                    class="is-link is-light"
                    :href="route(baseRouteName + '.index')"
                >
                    {{ i18n.cancel }}
                </biz-button-link>
            </div>
            <div class="control">
                <biz-button
                    class="is-link"
                    @click="onSubmit"
                >
                    {{ !isEditMode ? i18n.create : i18n.update }}
                </biz-button>
            </div>
        </div>
    </div>
</template>

<script>
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import MixinHasLoader from '@/Mixins/HasLoader';
    import Draggable from "vuedraggable";
    import FieldStructures from './../FieldStructures/index';
    import BizButton from '@/Biz/Button.vue';
    import BizButtonLink from '@/Biz/ButtonLink.vue';
    import BizCard from '@/Biz/Card.vue';
    import BizFormInput from '@/Biz/Form/Input.vue';
    import BizFormKey from '@/Biz/Form/Key.vue';
    import FieldGroup from './FieldGroup.vue';
    import InputConfig from './../Fields/InputConfig.vue';
    import { move as iconMove, remove as iconRemove } from '@/Libs/icon-class';
    import { isEmpty } from 'lodash';
    import { usePage } from '@inertiajs/vue3';
    import {
        isBlank,
        useModelWrapper,
        generateElementId,
        convertToKey,
    } from '@/Libs/utils';
    import { getEmptyFieldGroup } from './../Libs/form';
    import { confirmDelete } from '@/Libs/alert';

    export default {
        name: 'FormBuilder',

        components: {
            BizButton,
            BizButtonLink,
            BizCard,
            BizFormInput,
            BizFormKey,
            Draggable,
            FieldGroup,
            InputConfig,
        },

        mixins: [
            MixinHasPageErrors,
            MixinHasLoader,
        ],

        inject: {
            isEditMode: { default: false },
            i18n: { default: () => ({
                name : 'Name',
                form_id : 'Form ID',
                general : 'General',
                add_field_group : 'Add field group',
                cancel : 'Cancel',
                create : 'Create',
            }) },
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
                baseRouteName: usePage().props.baseRouteName,
                iconMove,
                iconRemove,
            };
        },

        data() {
            return {
                clonedComponent: null,
            };
        },

        computed: {
            computedFieldGroups() {
                return this.form.field_groups;
            },

            fieldByConfigId() {
                const self = this;

                let fieldGroupIndex = null;
                let fieldIndex = null;

                self.computedFieldGroups.forEach(function (fieldGroup, index) {
                    let fieldIndexTmp = fieldGroup.fields.findIndex(field => field.id == self.inputConfigId);

                    if (fieldIndexTmp !== -1) {
                        fieldGroupIndex = index;
                        fieldIndex = fieldIndexTmp;
                    }
                })

                return self.computedFieldGroups[fieldGroupIndex]?.fields[fieldIndex] ?? {};
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
                return isBlank(this.form.field_groups) ? false : this.form.field_groups.length > 0;
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

            onSettingInput(dataId) {
                this.computedInputConfigId = dataId;
            },

            addFieldGroup() {
                this.computedFieldGroups.push(getEmptyFieldGroup());
            },

            deleteFieldGroup(index) {
                const self = this;

                confirmDelete('Are you sure?').then((result) => {
                    if (result.isConfirmed) {
                        self.computedFieldGroups.splice(index, 1);
                    }
                })
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