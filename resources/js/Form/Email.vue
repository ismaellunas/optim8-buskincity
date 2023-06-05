<template>
    <form-input
        v-model="computedValue"
        type="email"
        :label="schema.label"
        :maxlength="schema.maxlength"
        :placeholder="schema.placeholder"
        :disabled="schema.is_disabled"
        :readonly="schema.is_readonly"
        :required="schema.is_required"
        :message="error(schemaName, bagName, errors)"
    >
        <template
            v-if="hasLeftIcon"
            #leftIcons
        >
            <biz-icon
                class="is-small is-left"
                :icon="schema.left_icon"
            />
        </template>

        <template
            v-if="schema.notes.length > 0"
            #note
        >
            <biz-field-notes
                type="info"
                :notes="schema.notes"
            />
        </template>
    </form-input>
</template>

<script>
    import BizFieldNotes from '@/Biz/FieldNotes.vue';
    import BizIcon from '@/Biz/Icon.vue';
    import FormInput from '@/Biz/Form/Input.vue';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import { isEmpty } from 'lodash';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'FormText',

        components: {
            BizFieldNotes,
            BizIcon,
            FormInput,
        },

        mixins: [
            MixinHasPageErrors,
        ],

        inject: [
            'bagName',
        ],

        props: {
            errors: {
                type: Object,
                default: () => {}
            },
            modelValue: {
                type: [String, Number, null],
                default: null
            },
            schema: {
                type: Object,
                required: true
            },
            selectedLocale: {
                type: [String],
                default: null
            },
        },

        setup(props, { emit }) {
            return {
                computedValue: useModelWrapper(props, emit),
            };
        },

        computed: {
            schemaName() {
                return this.schema.name;
            },

            hasLeftIcon() {
                return !isEmpty(this.schema.left_icon);
            },
        },
    };
</script>
