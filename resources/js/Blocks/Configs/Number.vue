<template>
    <div>
        <biz-form-number
            v-model="computedValue"
            :label="label"
            :is-small="true"
        >
            <template
                v-if="settings?.note"
                #note
            >
                <p
                    class="help"
                >
                    {{ settings?.note }}
                </p>
            </template>
        </biz-form-number>
    </div>
</template>

<script>
    import BizFormNumber from '@/Biz/Form/Number.vue';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'ConfigNumber',

        components: {
            BizFormNumber,
        },

        props: {
            label: { type: String, default: '' },
            modelValue: { type: [String, Number, null], required: true },
            settings: { type: Object, default: () => {} },
        },

        setup(props, { emit }) {
            return {
                computedValue: useModelWrapper(props, emit),
            };
        },
    }
</script>