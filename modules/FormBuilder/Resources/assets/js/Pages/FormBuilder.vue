<template>
    <form
        @submit.prevent="onSubmit"
    >
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
                            :list="availableComponents"
                            :group="{ name: 'components', pull: 'clone', put: false }"
                            :clone="cloneComponent"
                            :sort="false"
                            item-key="id"
                            @end="onEnd"
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

                        <draggable
                            class="dragColumnArea columns is-multiline"
                            :list="availableBlocks"
                            :group="{ name: 'columns', pull: 'clone', put: false }"
                            :clone="cloneBlock"
                            :sort="false"
                            item-key="name"
                            @change="log"
                        >
                            <template #item="{ element }">
                                <div class="column is-half">
                                    <div
                                        class="card"
                                    >
                                        <div class="card-content is-size-7">
                                            <div class="content">
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
                            v-model="form.fields.entities[contentConfigId]"
                            class="form-builder-content-config"
                        />
                    </template>
                </div>
            </div>

            <div
                class="column is-9"
                :class="{'has-background-grey-lighter has-text-centered': !hasBlok}"
            >
                <draggable
                    class="list-block-columns"
                    group="columns"
                    handle=".handle-columns"
                    item-key="id"
                    :animation="300"
                    :empty-insert-threshold="5"
                    :list="form.fields.structures"
                    :scroll-sensitivity="200"
                    :sort="true"
                >
                    <template #item="{element, index}">
                        <block-columns
                            :id="element.id"
                            v-model="form.fields.structures[index]"
                            v-model:data-entities="form.fields.entities"
                            :data-id="element.id"
                            @click="settingContent"
                            @delete-block="deleteBlock"
                            @duplicate-block="duplicateBlock"
                            @setting-content="settingContent"
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
                >
                    {{ !isEditMode ? 'Create' : 'Update' }}
                </biz-button>
            </div>
        </div>
    </form>
</template>

<script>
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import MixinHasLoader from '@/Mixins/HasLoader';
    import BizButton from '@/Biz/Button';
    import BizFormInput from '@/Biz/Form/Input';
    import BizButtonLink from '@/Biz/ButtonLink';
    import InputConfig from './../Blocks/InputConfig';
    import BlockColumns from './../Blocks/Columns'
    import blockColumnStructures from './../ComponentStructures/columns';
    import ComponentStructures from './../ComponentStructures';
    import Draggable from "vuedraggable";
    import { isBlank, useModelWrapper, generateElementId, getResourceFromDataObject } from '@/Libs/utils';
    import { createColumn } from './../Libs/form-builder.js';
    import { usePage } from '@inertiajs/inertia-vue3';
    import { cloneDeep } from 'lodash';

    export default {
        name: 'FormBuilder',

        components: {
            BizButton,
            BizButtonLink,
            BizFormInput,
            InputConfig,
            Draggable,
            BlockColumns,
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
            isComponentConfigOpen() {
                return (
                    !isBlank(this.contentConfigId)
                    && this.form.fields.entities[this.contentConfigId]
                );
            },

            availableComponents() {
                let components = [];
                for (const property in ComponentStructures) {
                    components.push(ComponentStructures[property]);
                }
                return components;
            },

            availableBlocks() {
                const maxColumnNumber = 6;
                let blocks = [];

                for (let i = 1; i <= maxColumnNumber; i++) {
                    let block = JSON.parse(JSON.stringify(blockColumnStructures));

                    for (let colIndex = 1; colIndex <= i; colIndex++) {
                        block.columns.push(createColumn());
                    }
                    block.id = '';
                    block.title = i+' Column'+((i>1) ? 's' : '');
                    blocks.push(block);
                }

                return blocks;
            },

            hasBlok() {
                return isBlank(this.form.fields.structures) ? false : this.form.fields.structures.length > 0;
            },
        },

        methods: {
            log: function(evt) {
                if (this.isDebugMode) {
                    console.log(evt);
                }
            },

            isComponentCloned(evt) {
                return (evt.pullMode === "clone");
            },

            onEnd(evt) {
                const component = this.clonedComponent;

                if (!this.isComponentCloned(evt)) {
                    delete this.form.fields.entities[component.id];
                }
            },

            cloneComponent(seletectedComponent) {
                this.clonedComponent = JSON.parse(JSON.stringify(seletectedComponent));
                this.clonedComponent.id = generateElementId();

                this.form.fields.entities[this.clonedComponent.id] = this.clonedComponent;

                return {
                    id: this.clonedComponent.id,
                    componentName: this.clonedComponent.componentName,
                };
            },

            cloneBlock(seletectedBlock) {
                const clonedBlock = JSON.parse(JSON.stringify(seletectedBlock));
                clonedBlock.id = generateElementId();

                delete clonedBlock.title;

                let columnEntity = JSON.parse(JSON.stringify(clonedBlock));

                delete columnEntity.columns;

                this.form.fields.entities[clonedBlock.id] = columnEntity;

                delete clonedBlock.config;

                return clonedBlock;
            },

            settingContent(event) {
                let configComponent = event.target.closest('.component-configurable');

                if (configComponent) {
                    if (configComponent.hasAttribute('data-id')) {
                        this.computedContentConfigId = configComponent.getAttribute('data-id');
                    }
                }
            },

            deleteBlock(id) {
                const removeIndex = this.form.fields.structures.map(block => block.id).indexOf(id);

                let removeIds = getResourceFromDataObject(
                    this.form.fields.structures[removeIndex],
                    'id'
                );

                this.form.fields.structures.splice(removeIndex, 1);

                for (let i = 0; i < removeIds.length; i++) {
                    delete this.form.fields.entities[removeIds[i]];
                }
            },

            duplicateBlock(id) {
                const duplicateIndex = this.form.fields.structures.map(block => block.id).indexOf(id);
                const duplicateBlock = cloneDeep(this.form.fields.structures[duplicateIndex]);

                duplicateBlock.id = generateElementId();

                duplicateBlock.columns.map(column => {
                    column.id = generateElementId();

                    column.components.map(component => {
                        const componentId = generateElementId();

                        this.duplicateEntity(component.id, componentId);

                        component.id = componentId;

                        return component;
                    });

                    return column;
                });

                this.form.fields.structures.splice( (duplicateIndex + 1), 0, duplicateBlock );

                this.duplicateEntity(id, duplicateBlock.id);
            },

            duplicateEntity(oldId, newId) {
                const duplicateEntity = cloneDeep(this.form.fields.entities[oldId]);
                duplicateEntity.id = newId;

                this.form.fields.entities[newId] = duplicateEntity;
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
        },
    }
</script>