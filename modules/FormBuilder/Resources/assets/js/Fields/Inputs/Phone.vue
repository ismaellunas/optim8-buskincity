<template>
    <div>
        <biz-toolbar-content
            @delete-content="$emit('delete-content', id)"
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
                v-if="hasNotes"
                #note
            >
                <biz-field-notes
                    type="info"
                    :notes="modelValue.notes"
                />
            </template>
        </biz-form-phone>
    </div>
</template>

<script>
    import MixinDuplicableContent from '@/Mixins/DuplicableContent';
    import MixinField from '@formbuilder/Mixins/Field';
    import BizFieldNotes from '@/Biz/FieldNotes.vue';
    import BizToolbarContent from '@/Blocks/Contents/ToolbarContent.vue';
    import BizFormPhone from '@/Biz/Form/Phone.vue';
    import { isEmpty } from '@/Libs/utils';

    export default {
        name: 'InputPhone',

        components: {
            BizFieldNotes,
            BizToolbarContent,
            BizFormPhone,
        },

        mixins: [
            MixinDuplicableContent,
            MixinField
        ],

        inject: [
            'countryOptions'
        ],

        emits: ['delete-content'],

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
