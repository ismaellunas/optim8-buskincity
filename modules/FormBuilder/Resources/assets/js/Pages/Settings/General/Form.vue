<template>
    <form @submit.prevent="$emit('on-submit')">
        <div class="columns">
            <div class="column">
                <h2>
                    <b>Submit Button Text</b>
                </h2>
            </div>

            <div class="column">
                <biz-form-input
                    v-model="form.submit_text"
                    :message="error('submit_text')"
                />
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
    import BizButton from '@/Biz/Button';
    import BizFormInput from '@/Biz/Form/Input';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'GeneralForm',

        components: {
            BizButton,
            BizFormInput,
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
        }
    }
</script>