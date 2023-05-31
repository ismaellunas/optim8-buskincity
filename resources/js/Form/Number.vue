<template>
    <biz-form-number
        v-model="computedValue"
        :label="schema.label"
        :maxlength="schema.maxlength"
        :placeholder="schema.placeholder"
        :disabled="schema.is_disabled"
        :readonly="schema.is_readonly"
        :required="schema.is_required"
        :message="error(schema.name, bagName, errors)"
    >
        <template
            v-if="schema.notes.length > 0"
            #note
        >
            <p
                class="help is-info"
            >
                <ul>
                    <li
                        v-for="(note, index) in schema.notes"
                        :key="index"
                    >
                        {{ note }}
                    </li>
                </ul>
            </p>
        </template>
    </biz-form-number>
</template>

<script>
    import BizFormNumber from '@/Biz/Form/Number.vue';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'FormNumber',

        components: {
            BizFormNumber,
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
                required: true
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
    };
</script>
