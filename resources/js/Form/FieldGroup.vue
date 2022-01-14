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

        <component
            :is="field.type"
            v-for="(field, name) in group.fields"
            :key="name"
            v-model="form[ name ]"
            :message="error(name, bagName, form.errors)"
            :schema="field"
        />
    </div>
</template>

<script>
    import Checkbox from './Checkbox';
    import CheckboxGroup from './CheckboxGroup';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import Number from './Number';
    import Phone from './Phone';
    import Radio from './Radio';
    import Select from './Select';
    import Text from './Text';
    import Textarea from './Textarea';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'FormFieldGroup',

        components: {
            Checkbox,
            CheckboxGroup,
            Number,
            Phone,
            Radio,
            Select,
            Text,
            Textarea,
        },

        mixins: [
            MixinHasPageErrors,
        ],

        inject: [
            'bagName',
        ],

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
            }
        },

        setup(props, { emit }) {
            return {
                form: useModelWrapper(props, emit),
            };
        }
    };
</script>
