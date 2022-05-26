<template>
    <form-input
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
        name: 'FormVideo',

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
        },

        setup(props, { emit }) {
            return {
                computedValue: useModelWrapper(props, emit),
            };
        },

        computed: {
            hasLeftIcon() {
                return !isEmpty(this.schema.left_icon);
            },
        },
    };
</script>
