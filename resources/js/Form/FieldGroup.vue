<template>
    <div :class="wrapperClass">
        <template v-if="group.title">
            <slot
                name="title"
                :title="group.title"
            >
                <h4 class="subtitle is-4 mt-4">
                    {{ group.title }}
                </h4>
            </slot>
        </template>

        <template
            v-for="(field, name) in group.fields"
            :key="name"
        >
            <component
                :is="field.type"
                v-if="!field.can_translate"
                :ref="'field__' + name"
                v-model="form[ name ]"
                :errors="form.errors"
                :schema="field"
            />

            <component
                :is="field.type"
                v-else
                :ref="'field__' + name"
                v-model="form[ name ][ selectedLocale ]"
                :errors="form.errors"
                :schema="field"
            />
        </template>
    </div>
</template>

<script>
    import Checkbox from './Checkbox';
    import CheckboxGroup from './CheckboxGroup';
    import File from './File';
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
            wrapperClass: {
                type: [Array, Object, String],
                default: "block"
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
        }
    };
</script>
