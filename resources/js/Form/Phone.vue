<template>
    <biz-form-phone
        v-model="computedValue"
        :label="schema.label"
        :maxlength="schema.maxlength"
        :placeholder="schema.placeholder"
        :disabled="schema.is_disabled"
        :readonly="schema.is_readonly"
        :required="schema.is_required"
        :message="message"
        :country-options="schema.countryOptions"
        :default-country="schema.defaultCountry"
    >
        <template #note>
            <p
                v-if="schema.note"
                class="help"
            >
                {{ schema.note }}
            </p>
        </template>
    </biz-form-phone>
</template>

<script>
    import BizFormPhone from '@/Biz/Form/Phone';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import { concat } from 'lodash';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'FormPhone',

        components: {
            BizFormPhone,
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
                type: Object,
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

        computed: {
            message() {
                return concat(
                    this.error(
                        this.schema.name+'.number',
                        this.bagName,
                        this.errors
                    ),
                    this.error(
                        this.schema.name+'.country',
                        this.bagName,
                        this.errors
                    )
                );
            },
        },
    };
</script>
