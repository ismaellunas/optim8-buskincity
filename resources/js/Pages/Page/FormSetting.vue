<template>
    <form @submit.prevent="$emit('on-submit')">
        <div class="columns">
            <div class="column">
                <h2>
                    <b>Template</b>
                </h2>
            </div>

            <div class="column">
                <biz-form-select
                    v-model="form.template"
                    :is-fullwidth="true"
                    :message="error('template')"
                >
                    <option :value="null">
                        (Default)
                    </option>
                    <option
                        v-for="option in templateOptions"
                        :key="option.id"
                        :value="option.id"
                    >
                        {{ option.value }}
                    </option>
                </biz-form-select>
            </div>
        </div>

        <div class="columns">
            <div class="column">
                <h2>
                    <b>Background Color</b>
                </h2>
            </div>

            <div class="column">
                <biz-form-select
                    v-model="form.background_color"
                    :is-fullwidth="true"
                    :message="error('background_color')"
                >
                    <option :value="null">
                        (Default)
                    </option>
                    <option
                        v-for="(option, index) in backgroundColorOptions"
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
                <h2>
                    <b>Page Height</b>
                </h2>
            </div>

            <div class="column">
                <biz-form-number-addons
                    v-model="form.page_height"
                    :message="error('page_height')"
                >
                    <template #afterInput>
                        <p class="control">
                            <a class="button is-static">
                                vh
                            </a>
                        </p>
                    </template>
                </biz-form-number-addons>
            </div>
        </div>
    </form>
</template>

<script>
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import BizFormNumberAddons from '@/Biz/Form/NumberAddons';
    import BizFormSelect from '@/Biz/Form/Select';
    import { useModelWrapper } from '@/Libs/utils';
    import { backgroundColors } from '@/ComponentStructures/style-options';

    export default {
        name: 'FormSetting',

        components: {
            BizFormNumberAddons,
            BizFormSelect,
        },

        mixins: [
            MixinHasPageErrors,
        ],

        inject: ['settingOptions'],

        props: {
            modelValue: { type: Object, required: true },
        },

        setup(props, {emit}) {
            return {
                form: useModelWrapper(props, emit),
            };
        },

        computed: {
            templateOptions() {
                return this.settingOptions.templates;
            },

            backgroundColorOptions() {
                return backgroundColors;
            },
        },
    }
</script>