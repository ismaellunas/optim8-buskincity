<template>
    <biz-form-radio
        v-model="computedValue"
        :disabled="schema.is_disabled"
        :label="schema.label"
        :message="error(schema.name, bagName, errors)"
        :required="schema.is_required"
        :options="schema.options"
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
    </biz-form-radio>
</template>

<script>
    import BizFormRadio from '@/Biz/Form/Radio.vue';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'FormRadio',

        components: {
            BizFormRadio,
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
    };
</script>
