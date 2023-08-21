<template>
    <biz-form-select
        v-model="computedValue"
        class="is-fullwidth"
        :disabled="schema.is_disabled"
        :label="schema.label"
        :message="error(schema.name, bagName, errors)"
        :placeholder="schema.placeholder"
        :readonly="schema.is_readonly"
        :required="schema.is_required"
    >
        <template
            v-for="option, index in schema.options"
            :key="index"
        >
            <option
                v-if="typeof option === 'object'"
                :value="option.id"
            >
                {{ option.value }}
            </option>

            <option
                v-else
                :value="index"
            >
                {{ option }}
            </option>
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
    </biz-form-select>
</template>

<script>
    import BizFieldNotes from '@/Biz/FieldNotes.vue';
    import BizFormSelect from '@/Biz/Form/Select.vue';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'FormSelect',

        components: {
            BizFieldNotes,
            BizFormSelect,
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
                default: ''
            },
            schema: {
                type: Object,
                required: true
            },
        },

        setup(props, { emit }) {
            return {
                computedValue: useModelWrapper(props, emit),
            };
        },

        mounted() {
            if (this.modelValue == '') {
                if (this.schema.default_value == this.modelValue) {
                    this.computedValue = null;
                }
            }
        },
    };
</script>
