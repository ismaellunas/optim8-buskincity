<template>
    <form-input
        v-model="computedValue"
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

        <template #note>
            <p
                v-if="schema.note"
                class="help"
            >
                {{ schema.note }}
            </p>
        </template>
    </form-input>
</template>

<script>
    import BizIcon from '@/Biz/Icon';
    import FormInput from '@/Biz/Form/Input';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import { isEmpty } from 'lodash';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'FormText',

        components: {
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
                let schemaName = this.schema.name;

                if (this.schema.is_translated) {
                    schemaName = schemaName + '.' + this.selectedLocale;
                }

                return schemaName;
            },

            hasLeftIcon() {
                return !isEmpty(this.schema.left_icon);
            },
        },
    };
</script>
