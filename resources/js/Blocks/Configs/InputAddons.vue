<template>
    <div>
        <biz-form-input-addons
            v-model="value"
            :label="label"
            @onKeypress="isNumber"
        >
            <template #afterInput>
                <p class="control">
                    <button
                        class="button"
                        tabindex="-1"
                        type="button"
                    >
                        {{ settings.addons }}
                    </button>
                </p>
            </template>
        </biz-form-input-addons>
    </div>
</template>

<script>
    import BizFormInputAddons from '@/Biz/Form/InputAddons';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'InputAddons',

        components: {
            BizFormInputAddons,
        },

        props: {
            label: { type: String, default: '' },
            modelValue: {},
            settings: { type: Object, default: () => {} },
        },

        setup(props, { emit }) {
            return {
                value: useModelWrapper(props, emit),
            };
        },

        methods: {
            isNumber(event) {
                let keyCode = (event.keyCode ? event.keyCode : event.which);

                if (
                    this.settings.isNumber
                    && (keyCode < 48 || keyCode > 57) && keyCode !== 46
                ) {
                    event.preventDefault();
                }
            },
        }
    }
</script>