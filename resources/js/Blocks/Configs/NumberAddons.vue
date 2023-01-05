<template>
    <div>
        <biz-form-number-addons
            v-model="value"
            :label="label"
            :is-small="true"
        >
            <template #afterInput>
                <p class="control">
                    <button
                        class="button is-small"
                        tabindex="-1"
                        type="button"
                    >
                        {{ settings.addons }}
                    </button>
                </p>
            </template>

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
        </biz-form-number-addons>
    </div>
</template>

<script>
    import BizFormNumberAddons from '@/Biz/Form/NumberAddons';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'ConfigNumberAddons',

        components: {
            BizFormNumberAddons,
        },

        props: {
            label: { type: String, default: '' },
            modelValue: { type: [Number, String, null], default: null },
            settings: { type: Object, default: () => {} },
        },

        setup(props, { emit }) {
            return {
                value: useModelWrapper(props, emit),
            };
        },
    }
</script>