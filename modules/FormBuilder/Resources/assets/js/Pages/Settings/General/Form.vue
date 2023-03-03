<template>
    <form @submit.prevent="$emit('on-submit')">
        <h2 class="title is-4 mt-5">
            Submit Button
        </h2>

        <div class="columns pl-2">
            <div class="column">
                <h2>
                    <b>Text</b>
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
                    <b>Position</b>
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
                <biz-button class="is-primary is-pulled-right">
                    Update
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