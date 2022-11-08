<template>
    <div class="card mb-5">
        <header
            v-if="group.title"
            class="card-header"
        >
            <slot
                name="title"
                :title="group.title"
            >
                <h3 class="card-header-title">
                    {{ group.title }}
                </h3>
            </slot>
        </header>

        <div class="card-content">
            <div class="columns is-multiline">
                <template
                    v-for="(field, name) in group.fields"
                    :key="name"
                >
                    <div
                        class="column"
                        :class="field.column ? field.column : `is-full`"
                    >
                        <component
                            :is="field.type"
                            v-if="!field.is_translated"
                            :ref="'field__' + name"
                            v-model="form[ name ]"
                            :errors="formErrors"
                            :schema="field"
                        />

                        <component
                            :is="field.type"
                            v-else
                            :ref="'field__' + name"
                            v-model="form[ name ][ selectedLocale ]"
                            :errors="formErrors"
                            :schema="field"
                            :selected-locale="selectedLocale"
                        />
                    </div>
                </template>
            </div>

            <slot name="note" />
        </div>
    </div>
</template>

<script>
    import Checkbox from './Checkbox';
    import CheckboxGroup from './CheckboxGroup';
    import Email from './Email';
    import File from './File';
    import FileDragDrop from './FileDragDrop';
    import Number from './Number';
    import Phone from './Phone';
    import Radio from './Radio';
    import Select from './Select';
    import Text from './Text';
    import Textarea from './Textarea';
    import Video from './Video';
    import { useModelWrapper, isEmpty } from '@/Libs/utils';

    export default {
        name: 'FormFieldGroup',

        components: {
            Checkbox,
            CheckboxGroup,
            Email,
            File,
            FileDragDrop,
            Number,
            Phone,
            Radio,
            Select,
            Text,
            Textarea,
            Video,
        },

        props: {
            group: {
                type: Object,
                default: () => {}
            },
            modelValue: {
                type: Object,
                required: true
            },
            selectedLocale: {
                type: String,
                default: null,
            },
            errors: {
                type: Object,
                default: () => {},
            },
        },

        setup(props, { emit }) {
            return {
                form: useModelWrapper(props, emit),
            };
        },

        computed: {
            formErrors() {
                if (this.form.errors) {
                    return this.form.errors;
                }

                return this.errors;
            },
        },
    };
</script>
