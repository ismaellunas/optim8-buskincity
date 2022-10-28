<template>
    <div>
        <biz-form-input
            v-model="computedValue"
            :label="label"
            :placeholder="settings?.placeholder"
        >
            <template
                v-if="settings?.note"
                #note
            >
                <p class="help">
                    {{ settings?.note }}
                </p>
            </template>
        </biz-form-input>
    </div>
</template>

<script>
    import BizFormInput from '@/Biz/Form/Input';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'Input',

        components: {
            BizFormInput,
        },

        props: {
            label: { type: String, default: '' },
            modelValue: { type: [String, null], required: true },
            settings: { type: Object, default: () => {} },
        },

        setup(props, { emit }) {
            return {
                computedValue: useModelWrapper(props, emit),
            };
        },
    }
</script>