<template>
    <div class="mb-3">
        <biz-form-input
            v-model="form.key"
            :label="i18n.key"
            required
            :message="error('key')"
            :disabled="isInputDisabled"
        />

        <biz-form-textarea
            v-if="referenceLocale != selectedLocale"
            v-model="form.value[referenceLocale]"
            :label="`English ${i18n.value}`"
            required
            disabled
        />

        <biz-form-textarea
            v-model="form.value[selectedLocale]"
            :label="i18n.value"
            required
            :message="error('value.' + selectedLocale)"
            :disabled="isInputDisabled"
        />
    </div>
</template>

<script>
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import BizFormInput from '@/Biz/Form/Input.vue';
    import BizFormTextarea from '@/Biz/Form/Textarea.vue';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'TranslationManagerForm',

        components: {
            BizFormInput,
            BizFormTextarea,
        },

        mixins: [
            MixinHasPageErrors,
        ],

        inject: {
            i18n: { default: () => ({
                key : 'Key',
                value : 'Value',
            }) },
        },

        props: {
            baseRouteName: {type: String, required: true},
            groupOptions: {type: Object, default: () => {}},
            modelValue: {},
            isInputDisabled: {type: Boolean, default: false},
            selectedLocale: {type: String, required: true},
            referenceLocale: {type: String, required: true},
        },

        setup(props, { emit }) {
            return {
                form: useModelWrapper(props, emit),
            };
        },
    }
</script>