<template>
    <form @submit.prevent="$emit('on-submit')">
        <h2 class="title is-4 mt-5">
            {{ i18n.submit_button }}
        </h2>

        <div class="columns pl-2">
            <div class="column">
                <h2>
                    <b>{{ i18n.text }}</b>
                </h2>
            </div>

            <div class="column">
                <biz-form-input
                    v-model="form.button.text"
                    :message="error('button.text')"
                />
            </div>
        </div>

        <div class="columns pl-2">
            <div class="column">
                <h2>
                    <b>{{ i18n.position }}</b>
                </h2>
            </div>

            <div class="column">
                <biz-form-select
                    v-model="form.button.position"
                    :message="error('button.position')"
                >
                    <option
                        v-for="(option, index) in buttonPositionOptions"
                        :key="index"
                        :value="option.value"
                    >
                        {{ option.name }}
                    </option>
                </biz-form-select>
            </div>
        </div>

        <div class="columns">
            <div class="column">
                <biz-button class="is-link is-pulled-right">
                    {{ i18n.update }}
                </biz-button>
            </div>
        </div>
    </form>
</template>

<script>
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import BizButton from '@/Biz/Button.vue';
    import BizFormInput from '@/Biz/Form/Input.vue';
    import BizFormSelect from '@/Biz/Form/Select.vue';
    import { useModelWrapper } from '@/Libs/utils';
    import { defaultOption, contentPositions } from '@/ComponentStructures/style-options';

    export default {
        name: 'GeneralForm',

        components: {
            BizButton,
            BizFormInput,
            BizFormSelect,
        },

        mixins: [
            MixinHasPageErrors,
        ],

        inject: {
            i18n: { default: () => ({
                submit_button: 'Submit Button',
                text: 'Text',
                position: 'Position',
                update: 'Update',
            }) },
        },

        props: {
            modelValue: { type: Object, required: true },
        },

        emits: [
            'on-submit',
        ],

        setup(props, {emit}) {
            return {
                form: useModelWrapper(props, emit),
            };
        },

        computed: {
            buttonPositionOptions() {
                return defaultOption.concat(contentPositions);
            }
        }
    }
</script>