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
            <template
                v-for="(field, name) in group.fields"
                :key="name"
            >
                <component
                    :is="field.type"
                    v-if="!field.is_translated"
                    :ref="'field__' + name"
                    v-model="form[ name ]"
                    field-class="mb-5"
                    :errors="form.errors"
                    :schema="field"
                />

                <component
                    :is="field.type"
                    v-else
                    :ref="'field__' + name"
                    v-model="form[ name ][ selectedLocale ]"
                    field-class="mb-5"
                    :errors="form.errors"
                    :schema="field"
                    :selected-locale="selectedLocale"
                />
            </template>
        </div>
    </div>
</template>

<script>
    import Checkbox from './Checkbox';
    import CheckboxGroup from './CheckboxGroup';
    import File from './File';
    import FileDragDrop from './FileDragDrop';
    import Number from './Number';
    import Phone from './Phone';
    import Radio from './Radio';
    import Select from './Select';
    import Text from './Text';
    import Textarea from './Textarea';
    import Video from './Video';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'FormFieldGroup',

        components: {
            Checkbox,
            CheckboxGroup,
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
                required: true,
            },
        },

        setup(props, { emit }) {
            return {
                form: useModelWrapper(props, emit),
            };
        },
    };
</script>
