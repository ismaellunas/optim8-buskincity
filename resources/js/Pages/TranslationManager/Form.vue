<template>
    <div class="mb-3">
        <biz-form-input
            v-model="form.key"
            label="Key"
            required
            :message="error('key')"
            :disabled="isInputDisabled"
        />

        <biz-form-textarea
            v-if="referenceLocale != selectedLocale"
            v-model="form.value[referenceLocale]"
            label="English Value"
            required
            disabled
        />

        <biz-form-textarea
            v-model="form.value[selectedLocale]"
            label="Value"
            required
            :message="error('value')"
            :disabled="isInputDisabled"
        />
    </div>
</template>

<script>
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import BizFormInput from '@/Biz/Form/Input';
    import BizFormTextarea from '@/Biz/Form/Textarea';
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