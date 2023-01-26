<template>
    <div>
        <biz-toolbar-content
            @delete-content="deleteContent"
            @duplicate-content="duplicateContent"
        />
        <biz-form-phone
            v-if="hasCountryOption"
            v-model="value"
            :label="modelValue.label"
            :required="modelValue.validation.rules.required"
            :disabled="modelValue.disabled"
            :readonly="modelValue.readonly"
            :placeholder="modelValue.placeholder"
            :country-options="computedCountryOptions"
        >
            <template
                v-if="modelValue.note"
                #note
            >
                <p
                    class="help"
                >
                    {{ modelValue.note }}
                </p>
            </template>
        </biz-form-phone>
    </div>
</template>

<script>
    import MixinDeletableContent from '@/Mixins/DeletableContent';
    import MixinDuplicableContent from '@/Mixins/DuplicableContent';
    import BizToolbarContent from '@/Blocks/Contents/ToolbarContent';
    import BizFormPhone from '@/Biz/Form/Phone';
    import { isEmpty } from '@/Libs/utils';

    export default {
        name: 'InputPhone',

        components: {
            BizToolbarContent,
            BizFormPhone,
        },

        mixins: [
            MixinDeletableContent,
            MixinDuplicableContent,
        ],

        inject: [
            'countryOptions'
        ],

        props: {
            id: { type: String, required: true },
            modelValue: { type: Object, required: true },
        },

        data() {
            return {
                value: {
                    country: 'US',
                    number: null,
                },
            };
        },

        computed: {
            computedCountryOptions() {
                return this.countryOptions();
            },

            hasCountryOption() {
                return !isEmpty(this.computedCountryOptions);
            },
        },
    };
</script>